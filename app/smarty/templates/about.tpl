{capture name="extra"}
{literal}
<style type="text/css" media="all,screen">
#content h2 {
	margin-top: 10px;
	margin-bottom: 10px;
	border-bottom: 2px solid #000;
}

.plug-in {
  overflow-y:absolute;
  overflow-x:hidden;
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

table.developers td {
	padding: 0 1em;
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
	<li>External application for research function: R 2.13.1, gnuplot 4.4
</ul>

<p>Currently, Win32 version is released. Win64 version and UNIX version will be available in the near future. </p>

<p>CIRCUS CS is a software free to download, free to use, and free to re-distribute (all for non-commercial use).
A plug-in development kit will be released in the winter 2011.</p>

<h3>Developer team:</h3>
<table class="developers">
	<tr style="padding-bottom: 15px">
		<td>- Yukihiro NOMURA, PhD</td>
		<td>overall coding, plugin development, and project management</td>
	</tr>
	<tr>
		<td>- Yoshitaka MASUTANI, PhD</td>
		<td>concept design, engineering supervision, and project direction</td>
	</tr>
	<tr>
		<td>- Naoto HAYASHI, MD PhD</td>
		<td>clinical supervision</td>
	</tr>
	<tr>
		<td>- Soichiro MIKI, MD</td>
		<td>clinical advice, and coding</td>
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

</table>

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
			<li>{$item.plugin_name|escape} v.{$item.version|escape} (installed in {$item.install_dt})
			<a href="plugin_info.php?pluginName='{$item.plugin_name|escape}&version={$item.version|escape}">detail</a>
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