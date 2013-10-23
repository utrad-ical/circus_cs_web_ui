  <div class="sorter-area {$sorterClass}"><form>
    {$sorter.label|escape}
    <select id="sorter" name="sortKey">
      {foreach from=$sorter item=sort}
      <option value="{$sort.key|escape}">{$sort.label|escape}</option>
      {/foreach}
    </select>
    <input type="radio" name="sortOrder" value="asc" id="sort-asc"/><label for="sort-asc">Asc.</label>&nbsp;
    <input type="radio" name="sortOrder" value="desc" id="sort-desc"/><label for="sort-desc">Desc.</label>
  </form></div><!-- /sorter -->