<a href="?">Home</a><br /> 
<?=$this->tplData['table']?> -
  <a href="?action=describe&table=<?=$this->tplData['table']?>">Describe</a>
| <a href="?action=layout&table=<?=$this->tplData['table']?>">Layout</a>
| <a href="?action=browse&table=<?=$this->tplData['table']?>">Browse</a>
-- <a href="?action=view&table=<?=$this->tplData['table']?>&id=<?=$_REQUEST['id']?>">View</a>
<br />

<style>
    table { border: solid 1px black; width: 800px; }
    label { text-align: right; width:100px; padding-right: 10px; display:inline-block; clear:both; }
    input {  }
    textarea { width: 100%; height: 80px; }
</style>
<form action="?" method="post">
    <?=$this->tplData['edit_layout']?>
    <br />
    <br />
    <input type="hidden" name="table" value="<?=$this->tplData['table']?>" />
    <input type="hidden" name="id" value="<?=$this->tplData['id']?>" />
    <input type="hidden" name="action" value="browse" />
    <input type="submit" name="action" value="save" />
    <input type="submit" value="cancel" />
    <br />
    <br />
</form>
