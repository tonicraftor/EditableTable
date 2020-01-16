<?php
  include "CMS_SessionCheck.php";
  include("config.php");
  define('ERRORSTR',[
    'TABLEINFO'=>'{"error":"Loading Table Information Failed!"}',
    'COLINFO'=>'{"error":"Loading Column Information Failed!"}',
    'TABLEDATA'=>'{"error":"Loading Table Data Failed!"}',
    'ADDNEW'=>'{"error":"Adding New Row Failed!"}',
    'DELROW'=>'{"error":"Deleting row failed!"}',
    'UPDATEROW'=>'{"error":"Updating row failed!"}'
  ]);
  if($_SERVER['REQUEST_METHOD']=='POST'){    
    $postarr = filter_input_array(INPUT_POST);
    $db = mysqli_connect(DB_SERVER,DB_USERNAME,DB_PASSWORD,DB_DATABASE);
    $tablename = mysqli_real_escape_string($db,$postarr['tbl_name']);
    $sql = "SELECT * FROM table_info WHERE name='$tablename' AND  auth_view>={$_SESSION['level']}";
    $result = mysqli_query($db,$sql);
    if (!$result||mysqli_num_rows($result)==0) {
      echo ERRORSTR['TABLEINFO'];
      exit(0);
    }
    $tableinfo = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    if($postarr['query_type']=='info'){
      $sql = "SELECT * FROM column_info WHERE table_id={$tableinfo['id']} AND  auth_view>={$_SESSION['level']}  ORDER BY id";
      $result = mysqli_query($db,$sql);
      if (!$result) {
        echo ERRORSTR['COLINFO'];
        exit(0);
      }
      $colinfo = mysqli_fetch_all($result,MYSQLI_ASSOC);
      mysqli_free_result($result);
      echo '{"title":"'.$tableinfo['title'].'",';
      echo '"count":'.$tableinfo['count'].',';
      echo '"col_info":[';
      $infostr = '';
      foreach($colinfo as $info){
        if($infostr)$infostr .= ',';
        $infostr .= '{"col_name":"'.$info['col_name'].'",'.
        '"col_title":"'.$info['col_title'].'",'.
        '"format":"'.$info['format'].'",'.
        '"format_info":"'.$info['format_info'].'",'.
        '"editable":'.($info['auth_edit']<$_SESSION['level']?0:1).'}';
      }
      echo $infostr.']}';
    }
    elseif($postarr['query_type']=='data'){
      $sql = "SELECT * FROM column_info WHERE table_id={$tableinfo['id']} AND  auth_view>={$_SESSION['level']}  ORDER BY id";
      $result = mysqli_query($db,$sql);
      if (!$result) {
        echo ERRORSTR['COLINFO'];
        exit(0);
      }
      $colinfo = mysqli_fetch_all($result,MYSQLI_ASSOC);
      mysqli_free_result($result);
      //get table content
      $querystr = '';
      foreach($colinfo as $info){
        if($querystr)$querystr .= ',';
        $querystr .= $info['col_name'];
      }
      if(!$querystr)exit(0);
      $sql = 'SELECT '.$querystr.' FROM '.$tablename;
      $maxrow = intval($postarr['max_row']);
      if($maxrow>0){
        $sql .= ' LIMIT '.intval($postarr['offset']).','.$maxrow;
      }
      $result = mysqli_query($db,$sql);
      if (!$result) {
        echo ERRORSTR['TABLEDATA'];
        exit(0);
      }
      $allrows = mysqli_fetch_all($result,MYSQLI_ASSOC);
      mysqli_free_result($result);
      echo '<tbody>';
      foreach ($allrows as $row){
        echo '<tr row-id="'.$row['id'].'">';
        foreach($row as $col){
          echo '<td>'.htmlspecialchars($col,ENT_QUOTES|ENT_HTML401).'</td>';
        }
        echo '</tr>';
      }
      echo '</tbody>';
    }
    elseif($postarr['query_type']=='add'){
      if($tableinfo['auth_edit']>=$_SESSION['level']){
        $sql = "SELECT col_name,default_val FROM column_info WHERE table_id={$tableinfo['id']}";
        $result = mysqli_query($db,$sql);
        if (!$result) {
          echo ERRORSTR['ADDNEW'];
          exit(0);
        }
        $colinfo = mysqli_fetch_all($result,MYSQLI_ASSOC);
        mysqli_free_result($result);
        $colstr = '';
        $valstr = '';
        foreach($colinfo as $info){
          if($info['default_val']!=NULL){
            if($colstr){
              $colstr.=',';
              $valstr.=',';
            }
            $colstr.=$info['col_name'];
            $valstr.='"'.$info['default_val'].'"';
          }
        }
        //echo "<br>".$valstr;
        $sql = "INSERT INTO $tablename ($colstr) VALUES ($valstr)";
        $result = mysqli_query( $db, $sql );
        if(!$result){
          echo ERRORSTR['ADDNEW'];
          exit(0);
        }
        $sql = "UPDATE table_info SET count = count+1 WHERE id={$tableinfo['id']}";		
        $result = mysqli_query( $db, $sql );
        if(!$result){
          echo ERRORSTR['ADDNEW'];
          exit(0);
        }
        echo $tableinfo['count']+1;
      }
      else{
        echo ERRORSTR['ADDNEW'];
        exit(0);
      }
    }
    elseif($postarr['query_type']=='del'){
      if($tableinfo['auth_edit']>=$_SESSION['level']){
        $row_id = intval($postarr['row_id']);
        $sql = "DELETE FROM $tablename WHERE id=$row_id";
        $result = mysqli_query( $db, $sql );
        if(!$result){
          echo ERRORSTR['DELROW'];
          exit(0);
        }
        $sql = "SELECT MAX(id) FROM $tablename";
        $result = mysqli_query( $db, $sql );
        if(!$result){
          echo ERRORSTR['DELROW'];
          exit(0);
        }
        $maxidarr = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        $maxid = $maxidarr["MAX(id)"]+1;
        if($maxid){
          $sql = "ALTER TABLE $tablename  AUTO_INCREMENT = $maxid";
          $result = mysqli_query( $db, $sql );
          if(!$result){
            echo ERRORSTR['DELROW'];
            exit(0);
          }
        }
        $sql = "UPDATE table_info SET count = count-1 WHERE id={$tableinfo['id']}";		
        $result = mysqli_query( $db, $sql );
        if(!$result){
          echo ERRORSTR['DELROW'];
          exit(0);
        }
        echo $tableinfo['count']-1;
      }
      else{
        echo ERRORSTR['DELROW'];
        exit(0);
      }
    }
    elseif($postarr['query_type']=='update'){
      if($tableinfo['auth_edit']>=$_SESSION['level']){
        $rowid = intval($postarr['row_id']);
        unset($postarr['tbl_name']);
        unset($postarr['query_type']);
        unset($postarr['row_id']);        
        
        $sql = "SELECT * FROM column_info WHERE table_id={$tableinfo['id']}";
        $result = mysqli_query( $db, $sql );
        if(!$result){
          echo ERRORSTR['UPDATEROW'];
          exit(0);
        }
        $colinfo = mysqli_fetch_all($result,MYSQLI_ASSOC);
        mysqli_free_result($result);
        
        $setstr = "";
        foreach($postarr as $key => $val){
          $prefix = substr($key,0,2);
          $key = substr($key,2);//cut off prefix
          if($prefix=='d_'){
            if(!array_key_exists('t_'.$key,$postarr))continue;
            $val .= ' '.$postarr['t_'.$key];
          }
          else if($prefix!='f_'){
            continue;
          }
          foreach($colinfo as $info){
            if($info['col_name']==$key&&$info['auth_edit']>=$_SESSION['level']){
              if($setstr)$setstr.=",";
              $setstr .= "$key='";
              if($info['format']=='password'){
                $setstr .= password_hash($val,PASSWORD_DEFAULT);
              }
              else{
                $setstr .= mysqli_real_escape_string($db, htmlspecialchars_decode($val,ENT_QUOTES|ENT_HTML401));
              }
              $setstr .= "'";
              break;
            }
          }
        }
        //update datetime now
        foreach($colinfo as $info){
          if($info['format']=='datetime'&&$info['format_info']=='now'){
            if($setstr)$setstr.=",";
            $setstr .= "{$info['col_name']}='".date('Y-m-d H:i:s')."'";
          }
        }
        if($setstr){
          $sql = "UPDATE $tablename SET $setstr WHERE id=$rowid";		
          $result = mysqli_query( $db, $sql );
          if(!$result){
            echo ERRORSTR['UPDATEROW'];
            exit(0);
          }
        }
        
        //upload image files
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $allowedTypes = array("image/gif","image/jpeg","image/jpg","image/pjpeg","image/x-png","image/png");
        foreach($_FILES as $key=>$fileobj){
          $filecol = 'f_'.substr($key,2);//replace prefix
          if(!array_key_exists($filecol,$postarr))continue;
          $filepath = $postarr[$filecol];
          $temp = explode(".", $fileobj['name']);
          $extension = strtolower(end($temp));
          if(in_array($fileobj["type"],$allowedTypes)&& in_array($extension, $allowedExts)){
            if ($fileobj["error"] == 0){
              switch ($extension) {
                case 'jpg':
                case 'jpeg':
                   $image = imagecreatefromjpeg($fileobj["tmp_name"]);
                break;
                case 'gif':
                   $image = imagecreatefromgif($fileobj["tmp_name"]);
                break;
                case 'png':
                   $image = imagecreatefrompng($fileobj["tmp_name"]);
                break;
              }
              imageinterlace($image,1);
              imagejpeg($image, $filepath);
              imagedestroy($image);
            }
          }
        }
        
        $querystr = '';
        foreach($colinfo as $info){
          if($info['auth_view']>=$_SESSION['level']){
            if($querystr)$querystr .= ',';
            $querystr .= $info['col_name'];
          }
        }
        
        if(!$querystr)exit(0);
        $sql = "SELECT $querystr FROM $tablename WHERE id=$rowid";
        $result = mysqli_query($db,$sql);
        if (!$result) {
          echo ERRORSTR['UPDATEROW'];
          exit(0);
        }
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        foreach($row as $col){
          echo '<td>'.htmlspecialchars($col,ENT_QUOTES|ENT_HTML401).'</td>';
        }
      }
      else{
        echo ERRORSTR['UPDATEROW'];
        exit(0);
      }
    }
    elseif($postarr['query_type']=='count'){
      echo $tableinfo['count'];
    }
  }
?>