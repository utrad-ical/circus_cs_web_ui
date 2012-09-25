<h2>Basic</h2>
<table class="col-tbl">
  <thead>
    <tr><th>Key</th><th>Value</th></tr>
  </thead>
  <tbody>
    <tr>
      <td class="name themeColor">CAD name</td>
      <td>{$cadResult->Plugin->plugin_name|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">CAD version</td>
      <td>{$cadResult->Plugin->version|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Job ID</td>
      <td>{$cadResult->job_id|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Job Status</td>
      <td>{$cadResult->status|status_str}</td>
    </tr>
    <tr>
      <td class="name themeColor">Executed time</td>
      <td>{$cadResult->executed_at|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Job registered time</td>
      <td>{$cadResult->registered_at|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Executed user</td>
      <td>{$cadResult->exec_user|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Patient Name</td>
      <td>{$series->Study->Patient->patient_name|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Patient ID</td>
      <td>{$series->Study->Patient->patient_id|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Patient Age</td>
      <td>{$series->Study->age|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Patient Sex</td>
      <td>{$series->Study->Patient->sex|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Plugin result policy</td>
      <td>{$cadResult->PluginResultPolicy->policy_name|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Result storage ID</td>
      <td>{$cadResult->Storage->storage_id|escape} ({$cadResult->Storage->path|escape})</td>
    </tr>
    <tr>
      <td class="name themeColor">Result storage path</td>
      <td>{$cadResult->pathOfCadResult()|escape}</td>
    </tr>
    <tr>
      <td class="name themeColor">Environment</td>
      <td>{if is_null($cadResult->environment)}<i>(Empty)</i>{else}{$cadResult->environment|escape}{/if}</td>
    </tr>
  </tbody>
</table>