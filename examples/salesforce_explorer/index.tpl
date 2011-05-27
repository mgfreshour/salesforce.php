<h1>All Tables</h1>
<table border="1">
    <? foreach($this->tplData['tables'] as $table): ?>
    <tr>
        <td><?=$table?></td>
        <td><a href="?action=describe&table=<?=$table?>">describe</a></td>
        <td><a href="?action=layout&table=<?=$table?>">layouts</a></td>
        <td><a href="?action=browse&table=<?=$table?>">browse</a></td>
    </tr>
    <? endforeach; ?>
</table>