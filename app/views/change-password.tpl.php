<header ><a href='<?=show_site('about')?>'><img src="<?= show_site('assets/images/logo.png')?>" alt="Meddie"/></a> <br/> 
<i>English word quiz</i>
</header>
<section>
    <h4>Change Your Passowrd</h4>
   <?php form_error()?>
<form method=post action = "<?php echo $form_action ?>" >
    <fieldset><legend>Old Password:</legend> <input type=password name=pswd1 /></fieldset>
    <fieldset><legend>New Password:</legend> <input type=password name=pswd2 /></fieldset>
    <fieldset><legend>Confirm New Password:</legend> <input type=password name=pswd3 /></fieldset>
    <input type=submit value='Change Password' class="submit_button2" /> <a href='<?= show_site('user')?>' class='submit_button1' >User</a>
</form>
    </section>