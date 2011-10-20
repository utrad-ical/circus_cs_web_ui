{capture name="extra"}
{literal}
<style type="text/css" media="all,screen">
#content h2 {
	margin-top: 10px;
	margin-bottom: 10px;
	border-bottom: 2px solid #000;
}

.plug-in {
	overflow-y: absolute;
	overflow-x: hidden;
	height: 100px;
	margin-left: 10px;
}

.machine-list {
  margin: 0 0 20px 10px;
}

h3 {
	margin: 2em 0 0.5em 0;
}

p {
	margin: 5px 0;
}

dl.developers dt {
	padding: 0 1em;
	display: block;
	float: left;
	width: 220px;
	height: 20px;
	overflow: hidden;
}

dl.developers dd {
	margin-left: 230px;
	min-height: 20px;
}

ol li {
	margin-left: 2em;
	list-style-type: decimal;
}
ul li {
	margin-left: 2em;
	list-style-type: disc;
}
</style>
{/literal}
{/capture}
{include file="header.tpl" head_extra=$smarty.capture.extra body_class="spot"}

<h2>About CIRCUS CS</h2>
<img src="images/circus-logo.jpg" width="231" height="170" align="right">

<p><b>CIRCUS CS</b> (Clinical Server) is a web based CAD process platform for clinical environment.
CIRCUS CS is developed under CIRCUS (Clinical Infrastructure for Radiologic Computation of United Solutions)
project in <a href="http://www.ut-radiology.umin.jp/ical/" target="blank">UTRAD ICAL</a>.</p>

<p>CIRCUS CS is based on the following technologies:</p>
<ul>
	<li>Web interface: Apache, PostgreSQL, PHP, PECL, jQuery, jQuery UI
	<li>DICOM storage server: DCMTK 3.6.0, PostgreSQL
	<li>External application for research function: R 2.13.2, gnuplot 4.4
</ul>

<p>Currently, Win32 and Win64 versions are released. UNIX version will be available in the near future. </p>

<h3>Developer team:</h3>
<dl class="developers">
	<dt>Yukihiro NOMURA, PhD</dt>
	<dd>overall coding, plugin development, and project management</dd>
	<dt>Yoshitaka MASUTANI, PhD</dt>
	<dd>concept design, engineering supervision, and project direction</dd>
	<dt>Naoto HAYASHI, MD PhD</dt>
	<dd>clinical supervision</dd>
	<dt>Soichiro MIKI, MD</dt>
	<dd>clinical advice, and coding</dd>
	<dt>Takeharu YOSHIKAWA, MD PhD</dt>
	<dd>clinical advice</dd>
	<dt>Mitsutaka NEMOTO, PhD</dt>
	<dd>plugin development, and coding advice</dd>
	<dt>Shouhei HANAOKA, MD PhD</dt>
	<dd>plugin development, and clinical advice</dd>
</dl>

<h3>References:</h3>
<ol>
	<li>Nomura Y, Hayashi N, Masutani Y, Yoshikawa T, Nemoto M, Hanaoka S, Maeda E, Ohtomo K,
		An integrated platform for development and clinical use of CAD software:
		building and utilization in the clinical environment,
		Int J CARS 4:S161-S162, June 2009</li>
	<li>Nomura Y, Hayashi N, Masutani Y, Yoshikawa T, Nemoto M, Ohtomo K, Hanaoka S, Maeda E,
		An integrated platform for clinical use of CAD software and feedback,
		Proc. of RSNA 2009:919 (LL-IN2158-R01), November 2009</li>
	<li>Nomura Y, Hayashi N, Masutani Y, Yoshikawa T, Nemoto M, Hanaoka S, Miki S, Maeda E, Ohtomo K,
		CIRCUS: an MDA platform for clinical image analysis in hospitals,
		Transactions on Mass-Data Analysis of Images and Signals, vol.2, no.1, pp.112-127, 2010</li>
</ol>

<h2>Installed plug-ins</h2>
<div class="plug-in">
	<ul>
		{foreach from=$pluginData item=item}
			<li><strong>{$item.plugin_name|escape}</strong> v.{$item.version|escape} (installed on {$item.install_dt})
			<a href="plugin_info.php?pluginName={$item.plugin_name|escape:'url'}&version={$item.version|escape:'url'}">detail</a>
		{/foreach}
	</ul>
</div>

<h2>Machine list</h2>
<div class="machine-list">
	<table class="col-tbl">
		<thead>
			<tr>
				<th>Host name</th>
				<th>IP address</th>
				<th>OS</th>
				<th>Architecture</th>
				<th>DICOM storage server</th>
				<th>Plug-in job manager</th>
		</thead>
		<tbody>
		{foreach from=$machineList item=item}
			<tr>
				<td class="al-l">{$item.host_name|escape}</td>
				<td class="al-l">{$item.ip_address|escape}</td>
				<td class="al-l">{$item.os|escape}</td>
				<td>{$item.architecture|escape}</td>
				<td>{$item.dicom_storage_server|OorMinus}</td>
				<td>{if $item.plugin_job_manager==1}controller mode{elseif $item.plugin_job_manager==2}process mode{elseif $item.plugin_job_manager==3}hybrid mode{else}-{/if}</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
</div>
{include file="footer.tpl"}