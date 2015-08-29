<?php
class Jasenyys extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
    }

    function tunnus($tunnus, $tila = ""){
	
	$this->load->library('Vrl_helper');
	 
	$fields = array();

	if (!($this->vrl_helper->check_vrl_syntax($tunnus) && $this->ion_auth->identity_check($tunnus)))
	{
	    $this->fuel->pages->render('jasenyys/tunnus', $fields);
	}
	else
	{
	    $pinnumber = $this->vrl_helper->vrl_to_number($tunnus);
	    
	    $user = $this->ion_auth->user()->row();
	    $this->load->model('Tunnukset_model');

	    $fields['tunnus'] = $this->vrl_helper->get_vrl($tunnus);
	    $fields['nimimerkki'] = $user->nimimerkki;
            $fields['email'] = $user->email;
            $fields['nayta_email'] = $user->nayta_email;
            
	    
	    $dateofbirth = date("d.m.Y", strtotime($user->syntymavuosi));
            $fields['syntymavuosi'] = $dateofbirth;
            $fields['sijainti'] = $user->laani;
            $fields['nayta_vuosilaani'] = $user->nayta_vuosilaani;
	    //TODO: Tarkasta onko yli 16v vai oliko se 15v??
           
	    $fields['muut_yhteystiedot'] = $this->tunnukset_model->get_users_contacts($pinnumber);
	    
	    
	    
	    
	    $this->fuel->pages->render('jasenyys/tunnus', $fields);
	}
	
    }
    function liity()
    {
	$this->load->library('form_validation');
        
        if($this->input->server('REQUEST_METHOD') == 'GET')
        {
            // load form_builder
            $this->load->library('form_builder', array('submit_value' => 'Liity', 'required_text' => '*Pakollinen kenttä'));
            $this->load->model('tunnukset_model');
	    $options = $this->tunnukset_model->get_location_option_list();

             
            // create fields
            $fields['nimimerkki'] = array('type' => 'text', 'required' => TRUE, 'after_html' => '<span class="form_comment">Nimimerkit eivät ole yksilöllisiä</span>', 'class'=>'form-control');
            $fields['email'] = array('type' => 'text', 'required' => TRUE, 'label' => 'Sähköpostiosoite', 'after_html' => '<span class="form_comment">esimerkki@osoite.fi</span>', 'class'=>'form-control');
            $fields['syntymavuosi'] = array('type' => 'text', 'label' => 'Syntymäaika', 'size' => '10', 'value' => '', 'after_html' => '<span class="form_comment">esim. 31.12.1999</span>', 'class'=>'form-control');
            $fields['sijainti'] = array('type' => 'select', 'options' => $options, 'first_option' => 'En halua kertoa', 'after_html' => '<span class="form_comment">Voit halutessasi laittaa iän ja sijainnin näkyväksi rekisteröitymisen jälkeen</span>', 'class'=>'form-control');
            $fields['roskapostitarkastus'] = array('type' => 'number', 'required' => TRUE, 'after_html' => '<span class="form_comment">Montako kaviota hevosella on? Numerona.</span>', 'class'=>'form-control');
            
            $this->form_builder->form_attrs = array('method' => 'post', 'action' => site_url('/jasenyys/liity'));
    
            // render the page
            $vars['join_form'] = $this->form_builder->render_template('_layouts/basic_form_template', $fields );
            
            $this->fuel->pages->render('jasenyys/liity', $vars);
        }
        else if($this->input->server('REQUEST_METHOD') == 'POST')
        {
            if($this->input->post('roskapostitarkastus') == '4')
            {
				$this->load->helper(array('form', 'url'));
                $this->load->model('tunnukset_model');
                $this->load->library('email');
                
                $this->form_validation->set_rules('nimimerkki', 'Nimimerkki', "required|min_length[1]|max_length[20]|regex_match[/^[A-Za-z0-9_\-.:,; *~#&'@()]*$/]");
                $this->form_validation->set_rules('email', 'Sähköpostiosoite', 'required|valid_email|is_unique[vrlv3_tunnukset.email]|is_unique[vrlv3_tunnukset_jonossa.email]');
                $this->form_validation->set_rules('syntymavuosi', 'Syntymäaika', 'min_length[8]|max_length[10]|callback__date_valid');
                $this->form_validation->set_rules('sijainti', 'Sijainti', 'min_length[1]|max_length[2]|numeric');

		if ($this->form_validation->run() == FALSE)
                {
		    $vars['join_msg'] = "Lomakkeen lähetys epäonnistui!";
		    $vars['join_msg_type'] = "danger";
                }
		else
		{
		    $vars['join_msg'] = "Lomakkeen lähetys onnistui!<br />Tarkasta antamasi sähköpostin postilaatikko (jos ei näy, katso roskapostikansio) ja seuraa lähetettyjä ohjeita.";
                    
                    $return_data = $this->tunnukset_model->add_new_application($this->input->post('nimimerkki'), $this->input->post('email'), $this->input->post('syntymavuosi'), $this->input->post('sijainti'));
                    
                    $this->email->from('jasenyys@virtuaalihevoset.net', 'Jäsenyyskone');
                    $this->email->to($this->input->post('email')); 
                    
                    $this->email->subject('Varmista VRL-tunnushakemuksesi!');
                    $this->email->message('Tervetuloa virtuaalisen ratsastajainliiton jäseneksi!\nVarmista lähettämäsi hakemus käymällä seuraavassa osoitteessa:\n\n---------------------------------------\n\nhttp://www.virtuaalihevoset.net/jasenyys/vahvista/?email=' . $this->input->post('email') . '&code=' . $return_data['varmistus'] . '\n\n---------------------------------------\n\nÄlä vastaa tähän sähköpostiin!\nJos et ole lähettänyt jäsenhakemusta, ota yhteys VRL:n ylläpitoon osoitteessa yllapito@virtuaalihevoset.net');
                    
                    //$this->email->send();
                    //TESTAAMATON --^
		}
            }
            else
            {
                $vars['join_msg'] = "Roskapostitarkastus epäonnistui. Olet botti.";
		$vars['join_msg_type'] = "danger";
            }
            
            $this->fuel->pages->render('jasenyys/liity', $vars);
        }
        else
            redirect('/', 'refresh');
    }
    
    function vahvista()
    {
        $this->load->model('tunnukset_model');
        
        $email = $this->input->get('email', TRUE);
        $code = $this->input->get('code', TRUE);
        
        if($email != false && $code != false && $this->tunnukset_model->validate_application($email, $code) == true)
            $vars['msg'] = "Sähköpostiosoitteesi vahvistaminen onnistui!<br /><br />Hakemuksesi siirtyy nyt tunnusjonoon, josta VRL:n työntekijä hyväksyy sen.<br />Saat tämän jälkeen sähköpostilla tunnuksen ja salasanan, joilla pääset kirjautumaan sisään.";
        else
            $vars['msg'] = "Jotain meni pieleen!<br /><br />Varmista, ettei sähköpostiisi tullut osoite katkennut osoitepalkille siirrettäessä ja yritä uudelleen.";
            
        $this->fuel->pages->render('misc/showmessage', $vars);
    }
    
    function _date_valid($date)
    {
        if($date == '' || preg_match('/^(?:(?:31(\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/', $date) == 1)
            return true;
        else
            return false;
    }
    
    
    
    
}
?>