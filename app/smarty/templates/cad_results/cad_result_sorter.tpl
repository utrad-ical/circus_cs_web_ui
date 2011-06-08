  {if $sorter.visible}
  <div id="sorterArea" style="text-align: right;"><form name="sorter">
    Sort:
    <select id="sorter" name="sortKey">
      {foreach from=$sorter.options item=sort}
      <option value="{$sort.key|escape}">{$sort.label|escape}</option>
      {/foreach}
    </select>
    <input type="radio" name="sortOrder" value="asc" id="sort-asc"/><label for="sort-asc">Asc.</label>&nbsp;
    <input type="radio" name="sortOrder" value="desc" id="sort-desc"/><label for="sort-desc">Desc.</label>
  </form></div><!-- /sorter -->
  {/if}