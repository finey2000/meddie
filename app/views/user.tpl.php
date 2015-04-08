<header id='quiz_header'><a href='<?=show_site('about')?>'><img src="<?= show_site('assets/images/logo.png')?>" alt="Meddie" /></a>   </header>
<h4 style='font-style:italic;'><?php echo $username?></h4>
<ul class='menu'>
    <li><a href='<?php echo $logout_url?>' class='submit_button2' >Logout</a></li>        
    <li><a href='<?php echo $delete_user_url?>' class='submit_button2'> Delete Account</a></li>    
    <li><a href='<?php echo $game_url?>' class='submit_button1'>Return To Game</a></li>        
    <li><a href='<?php echo $change_pswd_url?>' class='submit_button1'> Change Password</a></li>
</ul>
