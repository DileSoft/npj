<?php

// alias для инвертированного acl.php
// устанавливает флаг инвертирования и инклюдит acl.php
  $invert = true;
  return include ($rh->security_dir."acl.php");

?>