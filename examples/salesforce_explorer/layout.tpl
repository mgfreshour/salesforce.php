<a href="?">Home</a><br /> 
<?=$this->tplData['table']?> -
  <a href="?action=describe&table=<?=$this->tplData['table']?>">Describe</a>
| <a href="?action=layout&table=<?=$this->tplData['table']?>">Layout</a>
| <a href="?action=browse&table=<?=$this->tplData['table']?>">Browse</a>
<br />

<h2>Layouts</h2>
<style>
    table { border: solid 1px black; width: 700px; }
    label { text-align: right; width:150px; padding-right: 10px; display:inline-block; float: left; }
    input { float:left; }
    textarea { width: 300px; height: 80px; }
</style>

<h1>detailLayoutSections</h1><hr />
<?= $this->tplData['detail_layout'] ?>

<h1>editLayoutSections</h1><hr />
<?= $this->tplData['edit_layout'] ?>
