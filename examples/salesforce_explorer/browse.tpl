<a href="?">Home</a><br /> 
<?=$this->tplData['table']?> -
  <a href="?action=describe&table=<?=$this->tplData['table']?>">Describe</a>
| <a href="?action=layout&table=<?=$this->tplData['table']?>">Layout</a>
| <a href="?action=browse&table=<?=$this->tplData['table']?>">Browse</a>
<br />

<h2>Browse</h2>
<table border="1">
    <tr><th>x</th><th>x</th><th><?=implode('</th><th>',$this->tplData['field_headers'] )?></th></tr>
    <? foreach($this->tplData['tuples'] as $row): ?>
    <tr>
        <td><a href="?action=view&table=<?=$this->tplData['table']?>&id=<?=$row->Id?>">View</a></td>
        <td><a href="?action=edit&table=<?=$this->tplData['table']?>&id=<?=$row->Id?>">Edit</a></td>
        <? foreach($row->getAllFields() as $fld): ?>
            <td><?=$fld?></td>
        <? endforeach; ?>
    </tr>
    <? endforeach; ?>
</table>