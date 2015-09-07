<h2><?=$tunnus?>: <?=$nimimerkki?></h2>

<div class="container">
    <p><b>Nimimerkki:</b> <?=$nimimerkki?></p>
    <p><b>Rekisteröitynyt:</b> <?=$rekisteroitynyt?></p>
    
    <?php if ($logged_in){ ?>
        <p><b>Sähköpostiosoite:</b> <?=$email?></p>
        <p><b>Syntymäaika:</b> <?=$syntymavuosi?></p>
        <p><b>Sijainti:</b> <?=$sijainti?></p>
        <p>
            <b>Muut yhteystiedot:</b>
            <?php
                if(empty($muut_yhteystiedot))
                    echo "-";
                    
                echo "<ul>";
                
                foreach($muut_yhteystiedot as $my)
                {
                    echo "<li><b>" . $my['tyyppi'] . ": </b>" . $my['tieto'] . "</li>";
                }
                
                echo "</ul>";
            ?>
        </p>
        <p>
            <b>Vanhat nimimerkit:</b>
            <?php
                if(empty($nimimerkit))
                    echo "-";
                    
                echo "<ul>";
                
                foreach($nimimerkit as $n)
                {
                    echo "<li>" . $n['nimimerkki'] . "</li>";
                }
                
                echo "</ul>";
            ?>
        </p>
    <?php } else {?>
        <div class="alert alert-success" role="alert">
            Näet enemmän profiilitietoja sisäänkirjautuneena!
        </div>
    <?php }?>
    
    <ul class="nav nav-tabs">
    <li role="presentation" class="<?php if ($sivu == 'hevoset'){echo "active";}?>"><a href="<?php echo base_url('tunnus/'. $tunnus . '/hevoset')?>">Hevoset</a></li>
    <li role="presentation" class="<?php if ($sivu == 'tallit'){echo "active";}?>"><a href="<?php echo base_url('tunnus/'. $tunnus . '/tallit')?>">Tallit</a></li>
    <li role="presentation" class="<?php if ($sivu == 'kasvatit'){echo "active";}?>"><a href="<?php echo base_url('tunnus/'. $tunnus . '/kasvatit')?>">Kasvatit ja kasvattajanimet</a></li>
    <li role="presentation" class="<?php if ($sivu == 'kilpailut'){echo "active";}?>"><a href="<?php echo base_url('tunnus/'. $tunnus . '/kilpailut')?>">Kilpailut ja näyttelyt</a></li>
</ul>
</div>
