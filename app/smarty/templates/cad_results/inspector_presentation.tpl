<h2>Presentation Settings</h2>
<table class="col-tbl">
  <thead>
    <tr><th>Name</th><th>Parameters (Including Defaults)</th>
  </thead>
  <tbody>
    <tr>
      <td class="name themeColor">{$displayPresenter|get_class|escape}<br/>
      <em>(display presenter)</em></td>
      <td class="parameters">{$displayPresenter->getParameter()|@dumpParams}</td>
    </tr>
    <tr>
      <td class="name themeColor">{$feedbackListener|get_class|escape}<br/>
      <em>(feedback listener)</em></td>
      <td class="parameters">{$feedbackListener->getParameter()|@dumpParams}</td>
    </tr>
    {foreach from=$extensions key=num item=ext}
    <tr>
      <td class="name themeColor">{$ext|get_class|escape}<br/>
      <em>(extension #{$num})</em></td>
      <td class="parameters">{$ext->getParameter()|@dumpParams}</td>
    </tr>
    {/foreach}
  </tbody>
</table>
