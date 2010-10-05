<?xml version="1.0" encoding="shift_jis"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><!-- InstanceBegin template="/Templates/base.dwt" codeOutsideHTMLIsLocked="false" -->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=shift_jis" />
<meta http-equiv="content-style-type" content="text/css" />
<meta http-equiv="content-script-type" content="text/javascript" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<!-- InstanceBeginEditable name="doctitle" -->
<title>CIRCUS CS {$smarty.session.circusVersion}</title>
<!-- InstanceEndEditable -->

<link href="css/import.css" rel="stylesheet" type="text/css" media="all" />
<script language="javascript" type="text/javascript" src="jq/jquery-1.3.2.min.js"></script>
<script language="javascript" type="text/javascript" src="jq/jq-btn.js"></script>
<script language="javascript" type="text/javascript" src="js/hover.js"></script>
<script language="javascript" type="text/javascript" src="js/viewControl.js"></script>

<link rel="shortcut icon" href="favicon.ico" />
<!-- InstanceBeginEditable name="head" -->
<link href="./css/mode.{$smarty.session.colorSet}.css" rel="stylesheet" type="text/css" media="all" />

{literal}
<style type="text/css" media="all,screen">
<!--

#content h1 {
	font-size: 18px;
	margin-bottom: 3px;
}

#content h2 {
	margin-top: 10px;
	margin-bottom: 10px;
	border-bottom: 2px solid #000;
}

.plug-in {
  overflow-y:auto;
  overflow-x:hidden;
  height: 100px;
}

-->
</style>
{/literal}

<!-- InstanceEndEditable -->
</head>

<!-- InstanceParam name="class" type="text" value="home" -->
<body class="spot">
<div id="page">
	<div id="container" class="menu-back">
		<div id="leftside">
			{include file='menu.tpl'}
		</div><!-- / #leftside END -->
		<div id="content">
<!-- InstanceBeginEditable name="content" -->
			<h2>About CIRCUS CS</h2>
			<div class="ml10">
				<img src="images/circus-logo.jpg" width=231 height=170 align="right">
			
				<p>CIRCUS CS (Clinical Server) is a web based CAD process platform for clinical environment. 
				CIRCUS CS is developed under CIRCUS (Clinical Infrastructure for Radiologic Computation of United Solutions)
				project in <a href="http://www.ut-radiology.umin.jp/ical/" target="blank">UTRAD ICAL</a>.</p>

				<p class="mt5">CIRCUS CS is based as follows:</p>
				<ul class="mt5 ml10">
					<li>- Web interface: Apache, PostgreSQL, PHP, PECL, jQuery, jQuery-UI
					<li>- DICOM storage server: DCMTK 3.5.4, PostgreSQL
					<li>- External application for research function: R 2.10.2, gnuplot 4.4
				</ul>				
				
				<p class="mt5">Currently, Win32 version is released. Win64 version and UNIX version will be available in the near future. </p>

				<p class="mt5">CIRCUS CS is a software free to download, free to use, and free to re-distribute (all for non-commercial use).
				A plug-in development kit will be released in the autum 2010.</p> 

				<h4 class="mt20">Developer team:</h4>
				<table class="ml10 mt5 mb10">
					<tr>
						<td style="width:220px;">- Yukihiro NOMURA, PhD</td>
						<td>overall coding, plugin development, and project management</td>
					</tr>
					<tr><td colspan="2" style="height:5px;"></td></tr>
					<tr>
						<td>- Yoshitaka MASUTANI, PhD</td>
						<td>concept design, engineering supervision, and project direction</td>
					</tr>
					<tr>
						<td>- Naoto HAYASHI, MD PhD</td>
						<td>clinical supervision</td>
					</tr>
					<tr>
						<td>- Takeharu YOSHIKAWA, MD PhD</td>
						<td>clinical advice</td>
					</tr>
					<tr>
						<td>- Mitsutaka NEMOTO, PhD</td>
						<td>plugin development, and coding advice</td>
					</tr>
					<tr>
						<td>- Shouhei HANAOKA, MD PhD</td>
						<td>plugin development, and clinical advice</td>
					</tr>
					<tr>
						<td>- Soichiro MIKI, MD</td>
						<td>clinical advice, and coding advice</td>
					</tr>
				</table>

				<h4 class="mt10">References:</h4>
				<ol class="mt5 ml10">
					<li>Nomura Y, Hayashi N, Masutani Y, Yoshikawa T, Nemoto M, Hanaoka S, Maeda E, Ohtomo K,
                        An integrated platform for development and clinical use of CAD software:
                        building and utilization in the clinical environment,
                        Int J CARS 4:S161-S162, June 2009</li>
					<li>Nomura Y, Hayashi N, Masutani Y, Yoshikawa T, Nemoto M, Ohtomo K, Hanaoka S, Maeda E,
						An integrated platform for clinical use of CAD software and feedback,
						Proc. of RSNA 2009:919 (LL-IN2158-R01), November 2009</li>
					<li>Nomura Y, Hayashi N, Masutani Y, Yoshikawa T, Nemoto M, Hanaoka S, Miki S, Maeda E, Ohtomo K,
						CIRCUS: an MDA platform for clinical image analysis in hospitals,
						Transactions on Mass-Data Analysis of Images and Signals, vol.2, no.1 (In printing)</li>
				</ol>
			</div>


			<h2>Installed plug-ins</h2>
			<div class="plug-in ml10">
				<ul>
					{foreach from=$pluginData item=item}
						<li>{$item.plugin_name} v.{$item.version} (installed in {$item.install_dt})
						<a href="plugin_info.php?pluginName='{$item.plugin_name}&version={$item.version}">detail</a>
					{/foreach}
				</ul>
			</div>
<!-- InstanceEndEditable -->
		</div><!-- / #content END -->
	</div><!-- / #container END -->
</div><!-- / #page END -->
</body>
<!-- InstanceEnd --></html>

