<?php
  require_once 'config.php';
 

function getLabelsByTaskId($task_id)
{
$statement = "select label_id, label, is_default, step, class, is_computed, mark_all, is_skip FROM label where task_id = ".$task_id." order by label_order";
	$result=queryDb($statement);
	$keywords = array();
	while($row = $result->fetch_assoc()){
		$record = array();
		$record["label_id"]=$row["label_id"];
		$record["label"]=$row["label"];
		$record["is_default"]=$row["is_default"];
		$record["is_computed"]=$row["is_computed"];
		$record["is_skip"]=$row["is_skip"];

		$record["mark_all"]=$row["mark_all"];
		$record["step"]=$row["step"];
		$record["style_class"]=$row["class"];

		array_push($keywords, $record);
	}
	return $keywords;	
}
function getNextDocumentIdsByJob($job_id, $dataset_id, $limit)
{
	$statement = "select document_id from document d where d.dataset_id = $dataset_id and d.document_id not in (
	select distinct u.document_id from judgement jm, unit u where jm.job_id =$job_id 
	and u.unit_id = jm.unit_id) limit $limit"; 

	$documents["statement1"] = $statement;

	$result=queryDb($statement);
		$documentids = array();
		while($row = $result->fetch_assoc()){
			array_push($documentids, $row["document_id"]);
		}
		$in = "(".implode(",",$documentids).")";
		$statement2="SELECT u.document_id, u.unit_id, t.text FROM unit u, text t where u.unit_id = t.unit_id and document_id in ".$in;


/*	$statement = "SELECT d.document_id, (select count(job_id) from judgement jm, unit u where u.unit_id = jm.unit_id and u.document_id = d.document_id) as count
 FROM job j, document d where d.dataset_id = j.dataset_id and job_id =$job_id order by count asc limit $limit";*/
 //$statement = "select distinct d.document_id, t.text, u.unit_id, (select count(distinct j2.user_id) from document d2, judgement jm2, unit u2, job j2  where d2.document_id = u2.document_id and jm2.unit_id = u2.unit_id and jm2.job_id = j2.job_id  and d2.document_id = d.document_id) as count  from document d, unit u, text t where d.document_id = u.document_id  and d.dataset_id = $dataset_id and t.unit_id = u.unit_id and not exists ( select 1 from judgement jm, unit u3 where jm.job_id = $job_id  and jm.unit_id = u3.unit_id and u3.document_id = d.document_id) order by count asc limit $limit";

 //echo $statement;
	$result=queryDb($statement2);
	$keywords = array();
	
	while($row = $result->fetch_assoc()){
		$record = array();
		$record["document_id"]=$row["document_id"];
		$record["unit_id"]=$row["unit_id"];
		$record["text"]=$row["text"];
		array_push($keywords, $record);
	}
	return $keywords;	
}

function getNextTokensByTokenJob($job_id,$dataset_id, $limit)
{

$statement = "select document_id from document d where d.dataset_id = $dataset_id and d.document_id not in (
select distinct u.document_id from judgement jm, unit u where jm.job_id =$job_id 
and u.unit_id = jm.unit_id) order by rand() limit $limit"; 

$result=queryDb($statement);
	$documentids = array();
	while($row = $result->fetch_assoc()){
		array_push($documentids, $row["document_id"]);
	}
	$in = "(".implode(",",$documentids).")";
	$statement2 = "SELECT u.document_id, u.unit_id, t.token, t.pos_tag FROM unit u, token t where u.unit_id = t.unit_id and document_id in ".$in;
 $result=queryDb($statement2);
	$documents = array();
	$first = true; 
	$token = array();
	$newDocument=array();
	$currentDocumentId;
	$doc = array();

	while($row = $result->fetch_assoc()){
		if($first)
		{
			$doc = array();
			$currentDocumentId= $row["document_id"];
			$first=false;
		}
		else
		{
			if($currentDocumentId != $row["document_id"])
			{
				if(sizeOf($doc)>0) //save doc
				{ 	
					$newDocument["tokens"] = $doc; 
					$newDocument["document_id"] = $currentDocumentId;
					array_push($documents, $newDocument);
					$currentDocumentId= $row["document_id"];
						$doc = array();


				}
			}
		}		
		$token = array();
		$token["unit_id"]=$row["unit_id"];
		$token["token"]=$row["token"];
		$token["pos_tag"]=$row["pos_tag"];
		$token["label_id"]=null;
		array_push($doc, $token);
	}
		$newDocument=array();
		$newDocument["tokens"] = $doc; 
		$newDocument["document_id"] = $currentDocumentId;
		array_push($documents, $newDocument);
	return $documents;	
}

function getNextDocumentIdsByTokenJob($job_id, $limit)
{
	$statement = "SELECT d.document_id, (select count(job_id) from judgement jm, unit u where u.unit_id = jm.unit_id and u.document_id = d.document_id) as count
 FROM job j, document d where d.dataset_id = j.dataset_id and job_id =$job_id order by count asc limit $limit";
 //$statement = "select distinct d.document_id, t.text, u.unit_id, (select count(distinct j2.user_id) from document d2, judgement jm2, unit u2, job j2  where d2.document_id = u2.document_id and jm2.unit_id = u2.unit_id and jm2.job_id = j2.job_id  and d2.document_id = d.document_id) as count  from document d, unit u, text t where d.document_id = u.document_id  and d.dataset_id = $dataset_id and t.unit_id = u.unit_id and not exists ( select 1 from judgement jm, unit u3 where jm.job_id = $job_id  and jm.unit_id = u3.unit_id and u3.document_id = d.document_id) order by count asc limit $limit";

 //echo $statement;
	$result=queryDb($statement);
	$documents = array();

	while($row = $result->fetch_assoc()){
		$record = array();
		$record["document_id"]=$row["document_id"];
		array_push($documents, $record);
	}
	return $documents;	
}
function getJudgementForDocument($document_id, $job_id)
{
$statement = "SELECT u.unit_id, t.token_order, t.token, t.pos_tag, jm.label_id FROM judgement jm, unit u, token t ".
"where jm.unit_id = u.unit_id and t.unit_id = u.unit_id and u.document_id = $document_id and jm.job_id = $job_id";
	$result=queryDb($statement);
	$keywords = array();
	while($row = $result->fetch_assoc()){
		$record = array();
		$record["token"]=$row["token"];
		$record["unit_id"]=$row["unit_id"];
		$record["label_id"]=$row["label_id"];
		$record["pos_tag"]=$row["pos_tag"];
		$record["token_order"]=$row["token_order"];

		array_push($keywords, $record);
	}
	return $keywords;	
}
function getJobIdOrInsert($dataset_id, $task_id, $user_id)
{
	//injection test
	$statement = "SELECT job_id as value FROM job where dataset_id = $dataset_id and task_id = $task_id and user_id = $user_id";
	$job_id = querySelectSingleValue($statement);
	if($job_id == null)
	{
		$job_id = insertJob($dataset_id, $task_id, $user_id);
	}
	$job["job_id"] = $job_id;
	return $job;
}

function getUserIdByEmailOrInsert($email, $password)
{
	//injection test
	$statement = "SELECT user_id as value FROM user where email like '".$email."' and password like '".$password."'";
	$user_id = querySelectSingleValue($statement);
	if($user_id == null)
	{
		$user_id = insertUser($email, $password);
	}
	$user["user_id"] = $user_id;
	return $user;
}
function getUserIdByUsernameAndPassword($username, $password)
{
	//injection test
	$statement = "SELECT user_id as value FROM user where username like '".$username."' and password like '".$password."'";
	$user_id = querySelectSingleValue($statement);
	if($user_id == null)
	{
		$user["user_id"] = null;
	}
	else
	$user["user_id"] = $user_id;
	return $user;
}

function getUserIdByEmail($email)
{
	//injection test
	$statement = "SELECT user_id as value FROM user where email like '".$email."'";
	$user_id = querySelectSingleValue($statement);
	if($user_id == null)
	{
		$user_id = insertUser($email, '');
	}
	$user["user_id"] = $user_id;
	return $user;
}
function insertUser($email, $password)
{
	return insertDb("INSERT INTO user (email, password) VALUES ('".$email."', '".$password."')");
}
function getTokensByDocumentId($document_id, $job_id)
{
$statement="SELECT u.unit_id, t.token, t.pos_tag, jm.label_id FROM unit u left join (select unit_id, label_id from judgement where job_id = $job_id) as jm on (u.unit_id = jm.unit_id), token t 
where u.unit_id = t.unit_id and document_id = $document_id order by t.token_order asc";
$result=queryDb($statement);
	$keywords = array();
	while($row = $result->fetch_assoc()){
		$record = array();
		$record["unit_id"]=$row["unit_id"];
		$record["token"]=$row["token"];
		$record["pos_tag"]=$row["pos_tag"];
		$record["label_id"]=$row["label_id"];

		array_push($keywords, $record);
	}
	return $keywords;	
}
function getTextByDocumentId($document_id, $job_id)
{
	//TODO get label
	$statement = "SELECT u.unit_id, t.text FROM unit u, text t where u.unit_id = t.unit_id and document_id = $document_id";
	$result=queryDb($statement);
$record = array();
	while($row = $result->fetch_assoc()){
		
		$record["unit_id"]=$row["unit_id"];
		$record["text"]=$row["text"];
	}
	return $record;	
}
function insertJudgement($unit_id, $label_id, $job_id, $pos)
{
	$statement = 'INSERT INTO judgement (unit_id, job_id, label_id,pos, time) VALUES ( '.$unit_id.', '.$job_id.', '.$label_id.', "'.$pos.'", now())'.
	'  ON DUPLICATE KEY UPDATE label_id='.$label_id.' ,pos="'.$pos.'", time=now()';
	if(!queryDb($statement)){
		return false;
	}
	return $statement;	
}
function connect2Db()
{
	global $servername, $username, $password, $db;
	global $conn;
	
	
	if(isset($conn))
	{

	// Check connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}
	else
	{
			if (!$conn->set_charset("utf8")) {
			printf("Error loading character set utf8: %s\n", $conn->error);
		} 
	}
	}
	else
	{
		$conn = new mysqli($servername, $username, $password, $db);
	}
	return $conn;
}
function queryDb($query)
{	
	$conn = connect2DB();
	if(!$result = $conn->query($query)){
			
		echo 'ERROR: '.$conn->errno.'-'.$conn->error;
		return false;
	}
	else 
	{
		return  $result;
	}
}
function insertDb($query)
{	
	$conn = connect2DB();
	if(!$result = $conn->query($query)){
			
		echo 'ERROR: '.$conn->errno.'-'.$conn->error;
		return false;
	}
	else 
	{
		return  $conn->insert_id;
	}
}
function querySelectSingleValue($query)
{
	if($result = queryDb($query)){
		
		if(is_object($result))
		{
			$obj=$result->fetch_object();
			if(is_object($obj))
				return $obj->value;
		}
		return null;
		
	}
	else 
	echo "Error Select".$query;
}
function multiqueryDb($query)
{	
	$conn = connect2DB();
	if(!$result = $conn->multi_query($query)){
			
		echo 'ERROR: '.$conn->errno.'-'.$conn->error;
		return false;
	}
	else 
	{
		return  $result;
	}
}
function deleteJudgmentsDataset($dataset_id)
{
	$statement = "delete FROM judgement where job_id in (select job_id from job where dataset_id = $dataset_id)";
	return queryDb($statement);
}
function getNumberOfAnnotatedDocuments($dataset_id)
{
	$statement = "select sum(count) as value from (select distinct d.document_id, (select count(distinct jm2.job_id) 
from document d2, judgement jm2, unit u2  where d2.document_id = u2.document_id and jm2.unit_id = u2.unit_id 
and d2.document_id = d.document_id) as count
  from document d, unit u where d.document_id = u.document_id and d.dataset_id = $dataset_id) as x";
	$count = querySelectSingleValue($statement);
	
	$result["count"] = $count;
	return $result;
} 
function getIntrotext($dataset_id, $task_id)
{
	$statement = "SELECT text as value FROM introtext where dataset_id = $dataset_id and task_id = $task_id";
	$text = querySelectSingleValue($statement);
	
	$result["introtext"] = $text;
	return $result;
} 
function insertFeedback($job_id, $feedback_like, $feedback_use, $feedback_text)
{
	$statement = "insert into feedback(job_id, feedback_like, feedback_use, feedback_text) values ($job_id, '$feedback_like', '$feedback_use', '$feedback_text')";
	return insertDb($statement);	
}
function insertJob($dataset_id, $task_id, $user_id)
{
	return insertDb("insert into job(dataset_id, task_id, user_id) values ($dataset_id, $task_id, $user_id)");	
}
function insertUnit($document_id)
{
	return insertDb("insert into unit(document_id) values ($document_id)");	
}
function insertDocument($dataset_id, $org_document_id, $org_sentence_id)
{
	return insertDb("insert into document (dataset_id, org_document_id, org_sentence_id) values ($dataset_id,$org_document_id,$org_sentence_id)");	
}
function insertToken($unit_id, $token, $token_order, $pos_tag)
{
	return insertDb("insert into token(unit_id, token, token_order, pos_tag) values ($unit_id,'$token',$token_order,'$pos_tag')");	
}

function insertText($unit_id, $text)
{
	return insertDb("insert into text(unit_id, text) values ($unit_id,'$text')");	
}
function readTokensFromCSV($filepath, $dataset_id)
{
	$org_document_id = null;
	$org_sentence_id = 0;
	$token_order =1;
	
	$ix['document_id'] = 0;
	$ix['sentence_id'] = -1; 
	$ix['token']=1;
	$ix['pos_tag']=-1;
	$row=1;
				$conn = connect2DB();

if (($handle = fopen($filepath, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, $delimiter = "\t")) !== FALSE) {
	if($row == 1)
	{		
	}
	else
	{
		//if($org_document_id != $data[$ix['document_id']] || $org_sentence_id != $data[$ix['sentence_id']])
		if($org_document_id != $data[$ix['document_id']])
		{ 
			$org_document_id = $data[$ix['document_id']];
//			$org_sentence_id = $data[$ix['sentence_id']];
			$token_order=1;
			$document_id = insertDocument($dataset_id, $org_document_id, $org_sentence_id);
			
		}
		$token = $conn->real_escape_string($data[$ix['token']]);
		//$pos_tag = $conn->real_escape_string($data[$ix['pos_tag']]);
		$pos_tag = "";
		$unit_id = insertUnit($document_id);
		insertToken($unit_id, $token, $token_order, $pos_tag);		
		$token_order++;
	}
	        $row++;
    }
    fclose($handle);
}


}
function readTokensFromCSVSentence($filepath, $dataset_id)
{
	$org_document_id = null;
	$org_sentence_id = null;
	$token_order =1;
	
	$ix['document_id'] = 0;
	$ix['sentence_id'] = 1; 
	$ix['token']=2;
	$ix['pos_tag']=3;
	$row=1;
				$conn = connect2DB();

if (($handle = fopen($filepath, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, $delimiter = "\t")) !== FALSE) {
	if($row == 1)
	{		
	}
	else
	{
		if($org_document_id != $data[$ix['document_id']] || $org_sentence_id != $data[$ix['sentence_id']])
		{ 
			$org_document_id = $data[$ix['document_id']];
			$org_sentence_id = $data[$ix['sentence_id']];
			$token_order=1;
			$document_id = insertDocument($dataset_id, $org_document_id, $org_sentence_id);
			
		}
		$token = $conn->real_escape_string($data[$ix['token']]);
		$pos_tag = $conn->real_escape_string($data[$ix['pos_tag']]);
		$unit_id = insertUnit($document_id);
		insertToken($unit_id, $token, $token_order, $pos_tag);		
		$token_order++;
	}
	        $row++;
    }
    fclose($handle);
}


}
function readTextsFromCSV($filepath, $dataset_id)
{
	$org_document_id = null;
	$org_sentence_id = null;
	
	$ix['document_id'] = 0;
	$ix['text']=1;
	$row=1;
	
if (($handle = fopen($filepath, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, $delimiter = ",")) !== FALSE) {
	if($row == 1)
	{		
		echo "read header";
	}
	else
	{
			$org_document_id = $data[$ix['document_id']];
			$org_sentence_id = "''";
			$document_id = insertDocument($dataset_id, $org_document_id, $org_sentence_id);
			
	
			$conn = connect2DB();
		$text = $conn->real_escape_string($data[$ix['text']]);
		$unit_id = insertUnit($document_id);
		insertText($unit_id, $text);		
	}
	        $row++;
    }
    fclose($handle);
}
}




function writeResultsToCSVText($dataset_id,$user_id=null,$task_id=null, $type="token", $file_name="output/file.csv")
{
$fp = fopen($file_name, 'w');

	if($user_id!=null)
	$subquery = "and j.user_id = $user_id ";
	else
	$subquery = "";

	if($task_id!=null)
	$subquery2 = "and j.task_id = $task_id ";
	else
	$subquery2 = "";
	
	$statement="SELECT j.dataset_id, j.task_id, d.document_id, d.org_document_id,d.org_sentence_id, u.unit_id, REPLACE(REPLACE(REPLACE(t.$type, CHAR(13), ''), CHAR(10), ''), CHAR(9),'') as token, l.code as label, jm.time, j.user_id from judgement jm, job j, unit u, $type t, label l, document d
where jm.job_id = j.job_id and jm.unit_id = u.unit_id and t.unit_id = u.unit_id and jm.label_id = l.label_id and u.document_id = d.document_id
and j.dataset_id =$dataset_id ".$subquery.$subquery2."order by dataset_id, task_id, document_id";
//echo $statement;
$result=queryDb($statement);
$begin = true;
while($row = $result->fetch_assoc()){
if($begin)
{
	fputs($fp, implode(array_keys($row), chr(9))."\n");
	$begin=false;
}
 fputs($fp, implode($row, chr(9))."\n");

 
 //   fputcsv($fp, $row, chr(9));
	}
fclose($fp);

}
function getTypeforDatasetId($dataset_id)
{
	$statement="SELECT type as value FROM dataset where ds_id = $dataset_id"; 
	
	$value = querySelectSingleValue($statement);
	
	$result["type"] = $value;
	return $result;
}

function writeResultsToCSV($dataset_id, $file_name)
{
$fp = fopen($file_name, 'w');

	$statement="SELECT j.dataset_id, j.task_id, d.document_id, d.org_document_id,d.org_sentence_id, u.unit_id, REPLACE(REPLACE(REPLACE(t.token, CHAR(13), ''), CHAR(10), ''), CHAR(9),'') as token, t.token_order, concat(l.code,'-',jm.pos) as label, jm.time, j.user_id from judgement jm, job j, unit u, token t, label l, document d
where jm.job_id = j.job_id and jm.unit_id = u.unit_id and t.unit_id = u.unit_id and jm.label_id = l.label_id and u.document_id = d.document_id
and j.dataset_id =$dataset_id order by dataset_id, task_id, document_id, token_order";
$result=queryDb($statement);
$begin = true;
while($row = $result->fetch_assoc()){
if($begin)
{
	fputs($fp, implode(array_keys($row), chr(9))."\n");
	$begin=false;
}
 fputs($fp, implode($row, chr(9))."\n");

 
 //   fputcsv($fp, $row, chr(9));
	}
fclose($fp);

}
function writeResultsToCSVVoted($dataset_id,$user_id=null,$task_id=null, $type="token", $file_name="output/file.csv")
{
	$fp = fopen($file_name, 'w');

	
	if($user_id!=null)
	$subquery = "and j.user_id = $user_id ";
	else
	$subquery = "";

	if($task_id!=null)
	$subquery2 = "and j.task_id = $task_id ";
	else
	$subquery2 = "";
	
	if($type == "token")
	$tokenorder =  "t.token_order ,";
	else 
	$tokenorder = "";
	
	$statement = "select jm1.unit_id, jm1.label_id from  judgement jm1, job j 
    where  j.job_id = jm1.job_id  and j.dataset_id = $dataset_id ".$subquery.$subquery2."group by jm1.label_id, jm1.unit_id 
    having count(*) = ( select count(*) as count from  judgement jm2 where jm2.unit_id = jm1.unit_id
    group by jm2.label_id, jm2.unit_id order by count desc limit 1) order by jm1.unit_id";

	$result=queryDb($statement);
$begin = true;
	while($row = $result->fetch_assoc()){
	
		$unit_id = $row["unit_id"];
		$label_id = $row["label_id"];
		
		$statement2 = "	select y.unit_id, d.org_document_id,d.org_sentence_id, $tokenorder REPLACE(REPLACE(REPLACE(t.$type, CHAR(13), ''), CHAR(10), ''), CHAR(9),'') as token, y.label_id, l.label, l.code, sum(b) as b_pos, sum(i) as i_pos, count(*) as count
    from ( select jm3.unit_id, jm3.label_id, if(jm3.pos='B', 1,0) as b,if(jm3.pos='I', 1,0)  
    as i from  judgement jm3
    where jm3.unit_id = $unit_id  
    and jm3.label_id = $label_id) as y, unit u, $type t, label l, document d
    where 
    u.unit_id = y.unit_id and y.label_id = l.label_id and d.document_id = u.document_id
    and u.unit_id = t.unit_id
    group by y.unit_id, y.label_id order by $tokenorder unit_id";	

	
		$result2=queryDb($statement2);
		while($row = $result2->fetch_assoc()){
			
			$row["pos"] = $row["b_pos"] > $row["i_pos"] ? "B" : "I";
			if($begin)
{
	fputs($fp, implode(array_keys($row), chr(9))."\n");
	$begin=false;
}
 fputs($fp, implode($row, chr(9))."\n");
		}

	}
	fclose($fp);


}
function writeResultsToCSVByUser($user_id, $file_name)
{
$fp = fopen($file_name, 'w');

	$statement="SELECT j.dataset_id, j.task_id, d.document_id, d.org_document_id,d.org_sentence_id, u.unit_id, REPLACE(REPLACE(REPLACE(t.token, CHAR(13), ''), CHAR(10), ''), CHAR(9),'') as token, t.token_order, concat(l.code,'-',jm.pos) as label, jm.time, j.user_id from judgement jm, job j, unit u, token t, label l, document d
where jm.job_id = j.job_id and jm.unit_id = u.unit_id and t.unit_id = u.unit_id and jm.label_id = l.label_id and u.document_id = d.document_id
and j.user_id =$user_id order by dataset_id, task_id, document_id, token_order";
$result=queryDb($statement);
$begin = true;
while($row = $result->fetch_assoc()){
if($begin)
{
	fputs($fp, implode(array_keys($row), chr(9))."\n");
	$begin=false;
}
 fputs($fp, implode($row, chr(9))."\n");

 
 //   fputcsv($fp, $row, chr(9));
	}
fclose($fp);

}
function getJobsByUser($user_id)
{
 $statement ="SELECT j.job_id, j.limit, ds.name, t.title, t.type,t.showReset, ds.ds_id, t.task_id, t.maxStep FROM job j, task t, dataset ds  where j.user_id = $user_id and j.dataset_id = ds.ds_id and j.task_id= t.task_id";
 
 $result=queryDb($statement);
	$keywords = array();
	while($row = $result->fetch_assoc()){
		$record = array();
		$record["job_id"]=$row["job_id"];
		$record["dataset_name"]=$row["name"];
		$record["task_name"]=$row["title"];
		$record["task_type"]=$row["type"];
		$record["task_id"]=$row["task_id"];
		$record["dataset_id"]=$row["ds_id"];
		$record["task_maxStep"]=$row["maxStep"];
		$record["showReset"] =$row["showReset"];
		$record["task_limit"] =$row["limit"];

		array_push($keywords, $record);
	}
	return $keywords;	
}
function getProgressForJob($job_id)
{
	$statement="SELECT count(distinct u.document_id) as value from judgement j, unit u where j.unit_id = u.unit_id and j.job_id = $job_id"; 
	
	$count = querySelectSingleValue($statement);
	
	$result["count"] = $count;
	return $result;
}
function getDatasetProgress($dataset_id)
{
	//$statement = "select distinct d.document_id, (select count(distinct jm2.job_id) from document d2, judgement jm2, unit u2  where d2.document_id = u2.document_id and jm2.unit_id = u2.unit_id and d2.document_id = d.document_id) as count  from document d, unit u, text t where d.document_id = u.document_id and d.dataset_id = $dataset_id and t.unit_id = u.unit_id ";
	
	$statement = "select distinct d.document_id, (select count(distinct jm2.job_id) 
from document d2, judgement jm2, unit u2  where d2.document_id = u2.document_id and jm2.unit_id = u2.unit_id and d2.document_id = d.document_id) as count
  from document d, unit u where d.document_id = u.document_id and d.dataset_id = $dataset_id";
  
	
	$result=queryDb($statement);
	$keywords = array();
	while($row = $result->fetch_assoc()){
		$record = array();
		$record["document_id"]=$row["document_id"];
		$record["count"]=$row["count"];
		array_push($keywords, $record);
	}
	return $keywords;	
}
function getAllUserProgress()
{
$statement = "SELECT us.user_id, us.username, ds.name, t.title, count(distinct u.document_id) as count 
FROM judgement jm, job j, unit u, user us, document d,dataset ds, task t WHERE jm.job_id = j.job_id 
and jm.unit_id = u.unit_id and j.user_id = us.user_id and u.document_id = d.document_id and j.task_id = t.task_id
and ds.ds_id = d.dataset_id
group by 1,2,3,4";
$result=queryDb($statement);
	$keywords = array();
	while($row = $result->fetch_assoc()){
		$record = array();
		$record["user_id"]=$row["user_id"];
		$record["username"]=$row["username"];
		$record["documents"]=$row["count"];
		$record["task"]=$row["title"];
		$record["dataset"]=$row["name"];
		array_push($keywords, $record);
	}
	return $keywords;	
}
function getAllDatasetTasks()
{
$statement = "SELECT distinct j.dataset_id, ds.name, ds.type, t.task_id, t.title FROM job j, dataset ds, task t where j.dataset_id = ds.ds_id and t.task_id = j.task_id;";

	$result=queryDb($statement);
	$keywords = array();
	while($row = $result->fetch_assoc()){
		$record = array();
		$record["dataset_id"]=$row["dataset_id"];
		$record["dataset_name"]=$row["name"];
		$record["dataset_type"]=$row["type"];
		$record["task_id"]=$row["task_id"];
		$record["task_name"]=$row["title"];

		array_push($keywords, $record);
	}
	return $keywords;	
}
?>