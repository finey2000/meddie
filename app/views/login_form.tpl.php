<header ><a href='<?=show_site('about')?>'><img src="<?= show_site('assets/images/logo.png')?>" alt="Meddie"/></a> <br/> 
<i>English word quiz</i>
</header>
<section>
    <h4>User Login</h4>
   <?php form_error()?>
<form method=post action = "<?php echo $form_action ?>" >
    <fieldset><legend>Username:</legend> <input type=text name=username /></fieldset>
    <fieldset><legend>Password:</legend> <input type=password name=pswd /></fieldset>
    <input type=submit value=Login class="submit_button2" /> 
    <a href="<?php echo $new_account_src?>" class="submit_button2">New User</a>
</form>
    </section>
