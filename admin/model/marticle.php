<?php
class marticle extends Database {
	
	var $prefix = "api";
	function article_inp($data)
	{
		
		$date = date('Y-m-d H:i:s');
		$datetime = array();
        
		if(!empty($data['postdate'])){
            $data['postdate'] = date("Y-m-d H:i:s",strtotime($data['postdate']));
		}else{
            $data['postdate'] = $date;
		}
        if(!empty($data['expired_date'])) $data['expired_date'] = date("Y-m-d H:i:s",strtotime($data['expired_date']));
        else $data['expired_date'] = '0000-00-00';
        
        $data['title'] = mysql_escape_string($data['title']);
        $data['brief'] = mysql_escape_string($data['brief']);
        $data['content'] = mysql_escape_string($data['content']);

		if($data['action'] == 'insert'){
			
			$query = "INSERT INTO  
						{$this->prefix}_news_content (title,brief,content,image,file,categoryid,articletype,
												created_date,posted_date,expired_date,authorid,n_status)
					VALUES
						('".$data['title']."','".$data['brief']."','".$data['content']."','".$data['image']."'
                        ,'".$data['image_url']."','".$data['categoryid']."','".$data['articletype']."','".$date."'
                        ,'".$data['postdate']."','".$data['expired_date']."','".$data['authorid']."','".$data['n_status']."')";
                        //pr($query);exit;

		} else {
            if($data['categoryid']=='1' && $data['articletype']=='2' || $data['categoryid']=='8') $date = $data['postdate'];
			$query = "UPDATE {$this->prefix}_news_content
						SET 
							title = '{$data['title']}',
							brief = '{$data['brief']}',
							content = '{$data['content']}',
							image = '{$data['image']}',
							file = '{$data['image_url']}',
                            articletype = '{$data['articletype']}',
							posted_date = '{$data['postdate']}',
                            expired_date = '{$data['expired_date']}',
							authorid = '{$data['authorid']}',
							n_status = {$data['n_status']}
						WHERE
							id = '{$data['id']}'";
		}
// pr($query);
		$result = $this->query($query);
		
		return $result;
	}
	
	function get_article($categoryid=null,$type=1)
	{
		$query = "SELECT * FROM {$this->prefix}_news_content WHERE n_status = '1' AND categoryid = '{$categoryid}' OR n_status = '0' AND categoryid = '{$categoryid}' ORDER BY created_date DESC";
		
		$result = $this->fetch($query,1);

		foreach ($result as $key => $value) {
			$query = "SELECT username FROM admin_member WHERE id={$value['authorid']} LIMIT 1";

			$username = $this->fetch($query,0);

			$result[$key]['username'] = $username['username'];
		}
		
		return $result;
	}
    
    function get_article_filter($categoryid=null,$articletype=null,$type=1)
	{
		$query = "SELECT * FROM {$this->prefix}_news_content WHERE n_status = '1' AND categoryid = '{$categoryid}' AND articleType = '{$articletype}' OR n_status = '0' AND categoryid = '{$categoryid}' AND articleType = '{$articletype}' ORDER BY created_date DESC";
		
		$result = $this->fetch($query,0);
        
        if($result){
    		$query = "SELECT username FROM admin_member WHERE id={$result['authorid']} LIMIT 1";
    
    		$username = $this->fetch($query,0);
    
    		$result['username'] = $username['username'];
		}
		return $result;
	}
	
	function getData($categoryid=null,$articletype=null,$type=1, $id=false)
	{

		$filter = "";
		if ($id) $filter .= " AND id = {$id}";
		$query = "SELECT * FROM {$this->prefix}_news_content WHERE n_status = '1' AND categoryid = '{$categoryid}' AND articleType = '{$articletype}' OR n_status = '0' AND categoryid = '{$categoryid}' AND articleType = '{$articletype}' {$filter} ORDER BY created_date DESC";
		
		$result = $this->fetch($query,1);
        
        // pr($result);
        if($result){

        	foreach ($result as $key => $value) {

        		$query = "SELECT username FROM admin_member WHERE id={$value['authorid']} LIMIT 1";
    
	    		$username = $this->fetch($query,0);
	    
	    		$result[$key]['username'] = $username['username'];
        	}
    		
		}
		return $result;
	}

	function get_article_slide()
	{
		$query = "SELECT nc.*, cc.category, ct.type FROM cdc_news_content nc LEFT JOIN cdc_news_content_category cc 
					ON nc.categoryid = cc.id LEFT JOIN cdc_news_content_type ct ON nc.articletype = ct.id 
					WHERE nc.n_status < 2 AND nc.articletype > 0  ORDER BY nc.createdate DESC";
		
		$result = $this->fetch($query,1);
		
		return $result;
	}
	
	function get_article_trash($categoryid=null)
	{
		$query = "SELECT * FROM {$this->prefix}_news_content WHERE n_status = '2' AND categoryid = '{$categoryid}' ORDER BY created_date DESC";
		
		$result = $this->fetch($query,1);

		foreach ($result as $key => $value) {
			$query = "SELECT username FROM admin_member WHERE id={$value['authorid']} LIMIT 1";

			$username = $this->fetch($query,0);

			$result[$key]['username'] = $username['username'];
		}
		
		return $result;
	}
	
	function article_del($id, $action=null)
	{
		foreach ($id as $key => $value) {
			if($action == 'delete'){
                $query = "DELETE FROM {$this->prefix}_news_content WHERE id = '{$value}'";
			}else{
                $query = "UPDATE {$this->prefix}_news_content SET n_status = '2' WHERE id = '{$value}'";
            }
			$result = $this->query($query);
		
		}

		return true;
		
	}
	
	function article_delpermanent($id)
	{
		$query = "DELETE FROM cdc_news_content WHERE id = '{$id}'";
		
		$result = $this->query($query);
		
		return $result;
		
	}
	
	function article_restore($id)
	{
		foreach ($id as $key => $value) {
			
			$query = "UPDATE {$this->prefix}_news_content SET n_status = '0' WHERE id = '{$value}'";
		
			$result = $this->query($query);
		
		}

		return true;
		
	}
	
	function get_article_id($data)
	{
		$query = "SELECT * FROM {$this->prefix}_news_content WHERE id= {$data} LIMIT 1";
		
		$result = $this->fetch($query,0);

		//if($result['posted_date'] != '') $result['posted_date'] = dateFormat($result['posted_date'],'dd-mm-yyyy');
		($result['n_status'] == 1) ? $result['n_status'] = 'checked' : $result['n_status'] = '';

		return $result;
	}
	
	function frame_inp($data){

		foreach ($data[0] as $key => $val) {
			$tmpfield[] = $key;
			$tmpvalue[] = "'$val'";
		}

		$field = implode(',', $tmpfield);
		$value = implode(',', $tmpvalue);

		$query = "INSERT INTO {$this->prefix}_news_content_repo ({$field}) VALUES ($value)";

		$result = $this->query($query);

		$queryid = "SELECT id FROM {$this->prefix}_news_content_repo ORDER BY created_date DESC LIMIT 1";

		$id = $this->fetch($queryid,0);

		$data[1]['otherid'] = $id['id'];

		foreach ($data[1] as $key => $val) {
			$tmpfield2[] = $key;
			$tmpvalue2[] = "'$val'";
		}

		$field2 = implode(',', $tmpfield2);
		$value2 = implode(',', $tmpvalue2);

		$query2= "INSERT INTO {$this->prefix}_news_content_repo ({$field2}) VALUES ($value2)";

		$result = $this->query($query2);
		return true;
	}

	function get_frameList(){

		global $CONFIG;

		$query = "SELECT * FROM {$this->prefix}_news_content_repo WHERE gallerytype = 1 AND n_status = 1 ORDER BY created_date DESC";

		$result = $this->fetch($query,1);

		foreach ($result as $key => $val) {
			$query = "SELECT * FROM {$this->prefix}_news_content_repo WHERE gallerytype = 2 AND n_status = 1 AND otherid = {$val['id']} LIMIT 1";
			$res = $this->fetch($query,0);
			$result[$key]['cover'] = $res['files'];
			$result[$key]['covername'] = $res['title'];

			//typealbum
			if($val['typealbum'] == 4){
				$result[$key]['typealbum'] = 'Facebook';
			} elseif ($val['typealbum'] == 5) {
				$result[$key]['typealbum'] = 'Twitter';
			}
			
			//dimension
			list($result[$key]['frWidth'], $result[$key]['frHeight'], $type, $attr) = @getimagesize($CONFIG['admin']['upload_path']."frame/".$result[$key]['files']);
			list($result[$key]['covWidth'], $result[$key]['covHeight'], $type, $attr) = @getimagesize($CONFIG['admin']['upload_path']."cover/".$res['files']);
		}

		return $result;
	}

	function updateStatusFrame($id=false,$n_status=0)
	{
		if (!$id) return false;


		$query2= "UPDATE {$this->prefix}_news_content_repo SET n_status = {$n_status} WHERE id = {$id} LIMIT 1";

		$result = $this->query($query2);
		if ($result) return true;
		return false;

	}

	function insertNomenklatur($data)
	{

		pr($data);
		$i = 0;
		foreach ($data[0]['data'] as $key => $value) {

			$tmpFiled[] = $value;
			$i++;
		}

		foreach ($tmpFiled as $key => $value) {

			$tmpFiled1 = array();

			for ($i=0; $i <= 9 ; $i++) { 
				$tmpFiled1[] = "'".addslashes($value[$i])."'";
			}
			
			$tmp[] = "INSERT INTO api_nomenklatur (kdps_lama,nmps_lama,jen_lama,kdps_baru,nmps_baru,jen_baru,gelar_utama,singkatan_gelar,internasional_term,rumpun_txt) VALUES (". implode(',', $tmpFiled1).")";
			
		}

		foreach ($tmp as $key => $value) {
			
			$this->query($value);
		}
		// $filed = implode(',', $tmpFiled);
		// pr($tmp);
	}
}
?>