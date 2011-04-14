<?php
class PluginManager {
    var $plugins;
    // ==========================================================================
    function PluginManager()
    {
        include_once('codebase-php\bot_api.php');
    }
    // ==========================================================================
    function OpenTabForPlugin($name)
    {
        SendApi('BotApi.CreateNewTab=' . $name);
    }
    function RefreshPlugin($name, $url)
    {
        SendApi('BotApi.RefreshPlByName=' . $name . '|' . $url);
    }
    // ==========================================================================
    function RefreshMePlugin($name)
    {
        global $udpport;

        //$flp = file('tmp_dir\ports.txt');
        $url = 'http://127.0.0.1:' . ($udpport + 1) . '/Plugins/' . $name . '/Main.php?action=refresh&tmpv=' . rand(1, 10000);
        //echo $url;
        SendApi('BotApi.RefreshPlByName=' . $name . '|' . $url);
    }
    // ==========================================================================
    function OpenTabForAllPlugins()
    {
        foreach($this->plugins as $plugin) {
            if (($plugin['active'] == '1') || ($plugin['name']=='PluginsManager')) {
                $this->OpenTabForPlugin($plugin['name']);
            } else {
                $this->HideTabByName($plugin['name']);
            }
        }
    }
    // ==========================================================================
    function HideAllPluginsTab()
    {
        foreach($this->plugins as $plugin) {
            if ($plugin['name'] <> 'PluginsManager') {
                $this->HideTabByName($plugin['name']);
            }
        }
    }
    // ==========================================================================
    function HideTabByName($name)
    {
        SendApi('BotApi.HideTabByName=' . $name);
    }
    // ==========================================================================
    function ActivatePlugin($name, $action)
    {
        if ($action == "Activate") {
            if (!file_exists($this->plugins[$name]['folder'] . '\active.txt')) {
                $fl = fopen($this->plugins[$name]['folder'] . '\active.txt', 'w');
                fwrite($fl, '1');
                fclose($fl);

                $this->OpenTabForPlugin($name);
                $this->RefreshMePlugin($name);
            }
        } else {
            if (file_exists($this->plugins[$name]['folder'] . '\active.txt')) {
                unlink($this->plugins[$name]['folder'] . '\active.txt');
                $this->HideTabByName($name);
            }
        }
    }
    // ==========================================================================
    function GetConfiguration()
    {
        unset($this->plugins);

        $dir = getcwd() . '\Plugins';
        $dh = opendir($dir);

        if ($dh) {
            while (($file = readdir($dh)) !== false) {
                if (is_dir($dir . '/' . $file)) {
                    if ($file != '.' && $file != '..') {
                        $plugin = array();
                        $plugin['folder'] = 'Plugins\\' . $file;
                        $plugin['hooks'] = 'Plugins\\' . $file . '\Hooks.php';
                        $plugin['main'] = 'Plugins\\' . $file . '\Main.php';
                        if (file_exists('Plugins\\' . $file . '\active.txt')) {
                            $plugin['active'] = 1;
                        } else {
                            $plugin['active'] = 0;
                        }
                        $plugin['version'] = '';
                        $plugin['author'] = '';
                        $plugin['descr'] = '';
                        $plugin['link'] = '';
                        if (file_exists('Plugins\\' . $file . '\description.txt')) {
                            $fl = file('Plugins\\' . $file . '\description.txt');
                            foreach($fl as $stroka) {
                                $pos = strpos($stroka, '=');
                                $name = trim(substr($stroka, 0, $pos));
                                $val = trim(substr($stroka, $pos + 1, strlen($stroka)));

                                //$tmp = explode('=', $stroka);
                                $arr[$name] = $val;
                            }
                            $plugin['version'] = $arr['version'];
                            $plugin['author'] = $arr['author'];
                            $plugin['descr'] = $arr['descr'];
                            $plugin['link'] = $arr['link'];
                        }
                        $plugin['name'] = $file;
                        // print_r($plugin);
                        $this->plugins[$plugin['name']] = $plugin;
                    }
                }
            }
            closedir($dh);
        }
    }
    // ==========================================================================
    function GetEl($name)
    {
        if ($this->plugins[$name]['active'] == "1") {
            $active = "Deactivte";
            $status = "Enabled";
        } else {
            $active = "Activate";
            $status = "Disabled";
        }

        echo '
						<div style="width: 200px; padding-left: 12px; padding-top: 22px; height: 25px;" >
							<button id="btn" style="color:white;background-color:#13a89e;border-width:1px;border-style:solid; ">' . $active . '</button>
						</div>
						<div style="font-weight: bold; width: 200px; padding-left: 12px;" colspan="1">' . $name . '</div>
						<table style="margin-left: 10px;">
							<tr>
								<td width="100">Status: </td><td>' . $status . '</td>
							</tr>
							<tr>
								<td width="100">Version: </td><td>' . $this->plugins[$name]['version'] . '</td>
							</tr>
							<tr>
								<td>Author: </td><td>' . $this->plugins[$name]['author'] . '</td>
							</tr>
							<tr>
								<td>Site: </td><td><a style="width: 200px; overflow: hidden;" target="_blank" href="' . $this->plugins[$name]['link'] . '">Link to plugin homepage</a></td>
							</tr>
						</table>

							<div style="margin-left: 12px;"><br>Description:</div>
							<div style="margin-left: 6px; width: 180px;">
								<textarea disabled  name="comment" cols="43" rows="11">' . $this->plugins[$name]['descr'] . '</textarea>
							</div>
		';
    }
    // ==========================================================================
    function GetForm()
    {
        foreach($this->plugins as $plugin) {
            $options = $options . '<option value="' . $plugin['name'] . '">' . $plugin['name'] . '</option>';
        }

        echo '
<html>
	<head>
		<title>Neighbors helper</title>
		<style>
			body{
				background-color: rgb(74, 154, 74);
				font-size: 10pt;
			}
			#maind{
				font-size: 10pt;
				border: 1px solid black;
				width: 600px;
				height: 300px;
				background-color: rgb(255, 249, 200);
				padding: 0px;
			}
			#zag{
				font-size: 15pt;
				font-weight: bold;
			}
			#nsp{
				width: "200";
			}
		</style>

		<script src="..\..\codebase-php\jquery-1.4.2.min.js"></script>
		<script>
			$(document).ready(function(){
				$("#nsp").click(function(){
					var $l=window.location.toString();
					var $indx=$l.indexOf(\'?\');
					var $nurl=$l.slice(0, $indx)+"?action=get_el&name="+$(this).val()+"&tmp="+Math.random();
					$("#rightl").load($nurl);
				});

				$("#btn").live("click", function(){
					if ($("#nsp").val()!="PluginsManager"){
						var $l=window.location.toString();
						var $indx=$l.indexOf(\'?\');
						var $nurl=$l.slice(0, $indx)+"?action=activate&name="+$("#nsp").val()+"&state="+$("#btn").text()+"&tmp="+Math.random();

						$.get($nurl);
						setTimeout("$(\'#nsp\').click()",50);
						if ($("#btn").text()=="Activate"){
							$("#btn").text("Deactivate")
						}
						else{
							$("#btn").text("Activate");
						}
					}
				});

				$("#nsp").val($($("#nsp").find("option")[0]).val());
				$("#nsp").click();
			});
		</script>

	</head>
	<body>
		<form >
		<div  align="center">
			<div id="maind">
			<table border="0" width="600" height="300">
				<tr id="zag" height="30"><td colspan="2" align="center">Plugins Manager</td></tr>
				<tr>
					<td border="0" width="200" height="50">
					<div style="font-weight: bold; width:200;" align="center">Installed Plugins<div>
					<select size="23" multiple name="nlist" id="nsp">' . $options . '</select>
					</td>
					<td valign="top">
					<div id="rightl" height="250">
					</div>
					</td>
				</tr>
			</table>
			</div>
		</div>
		</form>
	</body>
</html>
		';
    }
}

?>
