<?php
/*
    DBAL( $config, $db_type="mysql" )  -- DBAL abstraction layer =), ADOdb one
      - �������� � ���� ������ �����������, ������� ������������� ����� � ��, 
        �������� � ���������� ������ �� ���� $config.
      - ��� ��������� ����� ������� ������ ����������� ���������/���������������� ADOdb


  ---------

  * DBAL_Error(...) -- �� ������ ����� �������, � ����� �������
      - ���������� ��� ������������� SQL-������
      - � index.php ������ �������������� ������� ���� $debug_hook = $rh->debug;

=============================================================== v.4 (Kuso)
*/

class DBAL
{
  var $config;
  var $dbtype;
  var $conn;

  function DBAL( &$config, $db_type="mysql" )
  {
     global $ADODB_FETCH_MODE;

     $config->UseLib("ADOConnection", $config->db_al_dir, "adodb.inc");

     $this->config = &$config;
     $this->db_type = $db_type;

     $this->quotes = array( "'" => "\'", "\\" => "\\\\" );

     $this->conn = NewADOConnection($db_type);
     $this->conn->Connect( $config->db_host, $config->db_user, $config->db_password, $config->db_name );
     $this->conn->raiseErrorFn = "DBAL_Error";
     $this->conn->rh = &$config;

     // turnoff numeric indexes for $rs->fields or something
     $ADODB_FETCH_MODE = 2;
  }

  function Close() { /* ??? ������ �� ��� ���-�� ����? */ }

// EOC{ DBAL } 
}


function DBAL_Error( $db_type, $more, $error_no, $error_msg, $sql, $input_arr )
{
  global $debug_hook;
  if (isset( $debug_hook )) 
  {
    $debug_hook->Trace( "Executing sql: <b>$sql</b>" );
    $debug_hook->Error( "DBAL SQL Error {".$error_no."} ".$error_msg );
  }
}

function DBAL_Error_Silent( $db_type, $more, $error_no, $error_msg, $sql, $input_arr )
{
  global $debug_hook;
  if (isset( $debug_hook )) 
  {
    $debug_hook->Trace( "Executing sql: <b>$sql</b>" );
    $debug_hook->Trace( "DBAL SQL Error {".$error_no."} ".$error_msg );
    $debug_hook->dbal_errors[] = $error_msg;
  }
}

?>