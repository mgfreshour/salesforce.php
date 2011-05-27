<a href="?">Home</a><br /> 
<?=$this->tplData['table']?> -
  <a href="?action=describe&table=<?=$this->tplData['table']?>">Describe</a>
| <a href="?action=layout&table=<?=$this->tplData['table']?>">Layout</a>
| <a href="?action=browse&table=<?=$this->tplData['table']?>">Browse</a>
<br />

<h2>Fields</h2>
<table border="1">
    <tr><th>Label</th><th>Col name</th><th>Type</th><th>Editable</th><th>Required</th></tr>
    <? foreach($this->tplData['fields'] as $field): ?>
        <tr>
            <td><?=$field->label?></td>
            <td><?=$name?></td>
            <td><?=$field->type?></td>
            <td><?=($field->updateable ? 'TRUE' : 'FALSE')?></td>
            <td><?=(($field->createable && !$field->nillable && !$field->defaultedOnCreate) ? 'TRUE' : '')?></td>
        </tr>
    <? endforeach; ?>
</table>

<h2>Relations</h2>
<table border="1">
    <tr><th>Label</th><th>Table Name</th><th>Field</th><th>Cascade Del</th><th>Deprecated</th></tr>
    <? foreach($this->tplData['relations'] as $name => $rel): ?>
        <tr>
            <td><?=$rel->relationshipName?></td>
            <td><?=$name?></td>
            <td><?=$rel->field?></td>
            <td><?=($rel->cascadeDelete ? 'TRUE' : 'FALSE')?></td>
            <td><?=($rel->deprecatedAndHidden ? 'TRUE' : 'FALSE')?></td>
        </tr>
    <? endforeach; ?>
</table>

