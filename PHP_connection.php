<?php
$con=mysqli_connect("15.164.89.139","root","super");

if(mysqli_connect_errno($con)){
  echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

mysqli_set_charset($con, "utf8");

$res = mysqli_query($con, "select * from users");

$result = array();

while($row = mysqli_fetch_array($res)){

  array_push($result, array('id'=>$row[0], 'email'=>$row[1], 'passward'=>$row[2], 'name'=>$row[3], 'school'=>$row[4]));
}
echo json_encode(array("result"=>$result));
mysqli_close($con);

?>
