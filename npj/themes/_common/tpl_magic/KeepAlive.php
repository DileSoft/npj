<?php
  if ($rh->keep_alive)  
  if ($rh->object->method == "edit")
  {  ?>
 <!-- Session Keep Alive -->
   <img src="<?php echo $tpl->GetValue("images");?>z.gif" name="sessionTicker" id="sessionTicker" 
        width="1" height="1" border="0" alt="" />
  <script language=javascript>
   refreshTicker( "<?php echo $rh->user_pictures_dir; ?>", <?php echo $rh->keep_alive; ?> );
  </script>
 <!-- /Session Keep Alive -->
<?php } 

  if ($tpl->theme == "minikui") echo "<br /><br />";

?>
Время:&nbsp;<?php 
     $m = $debug->_getmicrotime();
     $diff = $m - $debug->_milestone;
     echo sprintf("%0.4f",$diff);
?>&nbsp;с.

<?php
$pg = $object->data["supertag"]." = ".$rh->url;
$s_addurl="stat/";
//@include ($s_addurl."counter.php"); 
?>
