<?php
include('\Class\wikia.php');
?>
<html>
<link rel=stylesheet type="text/css" href="CSS/Lucifer.css">
</haed>
<script type="text/javascript" src="JS/js.js"></script>
<body>
<center><h2>CityvVille Wikia Ver 0.3b</h2></center>
<?php
$Inf = new wikia();
$Inf->Init();
print $Inf->show('');
?>
</body>
</html>