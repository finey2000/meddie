<header ><a href='<?=show_site('about')?>'><img src="<?= show_site('assets/images/logo.png')?>" alt="Meddie"/></a> <br/> 
<i>English word quiz</i>
</header>
<section>
    <h4>New User</h4>
   <?php form_error()?>
<form method=post action = "<?php echo $form_action ?>" >
    <fieldset><legend>Username:</legend> <input type=text name=username /></fieldset>
    <fieldset><legend>Email:</legend> <input type=text name=email /></fieldset>
    <fieldset><legend>Password:</legend> <input type=password name=pswd /></fieldset>
    <fieldset><legend>Confirm Password:</legend> <input type=password name=pswd2 /></fieldset>
    <input type=submit value='Create Account' class="submit_button2" /> 
</form>
    </section>