<html>
<head>
<script type="text/javascript" src="vis.min.js"></script>
<link href="vis.min.css" rel="stylesheet" type="text/css" />
<style type="text/css">
#mynetwork {
    width: 600px;
    height: 400px;
    border: 1px solid lightgray;
}
</style>
</head>
<body>
<?php
$interface='wlan1';
echo "<pre>";
$res="";
exec ( "iw $interface station dump",$res);

foreach ($res as $line) {
    $arr = explode(' ',trim($line),2);
    if ($arr[0]=='Station') {
        if (isset($mesh)) {
            $nodes[]=$mesh; 
            unset($mesg); 
        }
        $tmp=explode(' ',$arr[1],2); 
        $mesh['mac']=$tmp[0]; //$arr[1]; 
    } else {
        $arr = explode(':',trim($line),2); 
        $mesh[$arr[0]]=$arr[1]; 
    }
}
$nodes[]=$mesh; 
 

echo "<pre>"; 
?>
<h1>Nodes connected via
  <?=$interface?>
</h1>
<table border=1>
  <tr>
    <td>Mac</td>
    <Td>Inactive Tiem</td>
    <td>RX Bytes</td>
    <td>TX Bytes</td>
    <td>Signal</td>
    <td>Signal avg</td>
  </tr>
  <?php
foreach ($nodes as $node) {
?>
  <tr>
    <td><?=$node['mac']?></td>
    <td><?=$node['inactive time']?></td>
    <td><?=$node['rx bytes']?></td>
    <td><?=$node['tx bytes']?></td>
    <td><?=$node['signal']?></td>
    <td><?=$node['signal avg']?></td>
  </tr>
  <?php } ?>
</table>

<div id="mynetwork"></div>

<script type="text/javascript">
    // create an array with nodes
    var nodes = new vis.DataSet([
<?php
        $c=0;
        foreach ($nodes as $node) {
        $c++;
?>
{id:<?=$c?>, label: '<?=$node['mac']?>'},
<?php
}
?>
{id:0, label:'this'}
    ]);

    // create an array with edges
    var edges = new vis.DataSet([

<?php
$c=0;
foreach ($nodes as $node) {
    $c++;
    if ($c>1) echo ",";
    $res=explode(' ',trim($node['signal avg']));
    $dbm=$res[0];
    $color='grey';

    if ($dbm>=-50) {
        $color='blue';
    } elseif ($dbm<=-70) {
        $color="red";
    } elseif ($dbm<=-60) {
        $color="yellow";
    } elseif ($dbm<=-50) {
        $color="green";
    }
?>
        {from: 0, to: <?=$c?>, color: '<?=$color?>', label: '<?=$dbm?>dBm' }
<?php } ?>
    ]);

    // create a network
    var container = document.getElementById('mynetwork');

    // provide the data in the vis format
    var data = {
        nodes: nodes,
        edges: edges
    };
    var options = {};

    // initialize your network!
    var network = new vis.Network(container, data, options);
</script>
<?php
$res="";

exec("/opt/cjdns/tools/peerStats", $res);



unset($nodes);
unset($mesh);


foreach ($res as $line) {
        $arr = explode(' ',trim($line),2);
        $arr2 = explode('.',trim($arr[0]));
        $pub=$mesh['publickey']=$arr2[5] . ".k";
$mesh['ipv6']=exec("/opt/cjdns/publictoip6 $pub");
        $mesh['version']=$arr2[0];

        $arr2 = explode(' ',trim($arr[1]));
        $mesh['status']=$arr2[0];
        $mesh['rx']=$arr2[2];
        $mesh['tx']=$arr2[4];

$nodes[]=$mesh;


}



?>
<h1>Nodes connected via CJDNS</h1>
<table border=1>
  <tr>
    <td>Public Key</td>
    <td>IPV6</td>
    <Td>Version</td>
    <td>RX</td>
    <td>TX</td>
  </tr>
  <?php
foreach ($nodes as $node) {
?>
  <tr>
    <td><?=$node['publickey']?></td>
    <td><?=$node['ipv6']?></td>
    <td><?=$node['version']?></td>
    <td><?=$node['rx']?></td>
    <td><?=$node['tx']?></td>
  </tr>
  <?php } ?>
</table>
</body>
</html>
