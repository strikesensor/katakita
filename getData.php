<?php 
 error_reporting( E_ALL ); 
 ini_set('display_errors', 'on');
require_once('simple_html_dom.php');
require_once 'config.php';
require_once 'functions.php';

if(isset($_POST["method"]))
{
	switch($_POST["method"])
	{
		case "getJobsByUser":
			if(isset($_POST["user_id"])){
				echo json_encode(getJobsByUser($_POST["user_id"]));
			}
			break;	
		case "deleteJudgmentsDataset":
			if(isset($_POST["dataset_id"])){
			$data = deleteJudgmentsDataset($_POST["dataset_id"]);
			echo json_encode($data);	}
			break;
		case "getNumberOfAnnotatedDocuments":
		if(isset($_POST["dataset_id"])){
			$data = getNumberOfAnnotatedDocuments($_POST["dataset_id"]);
			echo json_encode($data);	}
			break;
		case "insertFeedback":
			$data = insertFeedback($_POST["job_id"], $_POST["feedback_like"], $_POST["feedback_use"], $_POST["feedback_text"]);
			echo json_encode($data);	
			break;
		case "getDatasetProgress":
			if(isset($_POST["dataset_id"]))
			{
				$data = getDatasetProgress($_POST["dataset_id"]); 
				echo json_encode($data);	
			}
			break;
		case "getTextByDocumentId":
			if(isset($_POST["document_id"])& isset($_POST["job_id"]))
			 {
				$data = getTextByDocumentId($_POST["document_id"],$_POST["job_id"]); 
				echo json_encode($data);	
			}
			break;
			
		case "getNextDocumentIdsByTokenJob": 
			if(isset($_POST["job_id"]) & isset($_POST["limit"]))
			{
				$data = getNextDocumentIdsByTokenJob($_POST["job_id"],$_POST["limit"]);
				echo json_encode($data);			
			}
			break;
			case "getNextTokensByTokenJob": 
			if(isset($_POST["job_id"]) & isset($_POST["limit"]) & isset($_POST["dataset_id"]))
			{
				$data = getNextTokensByTokenJob($_POST["job_id"],$_POST["dataset_id"],$_POST["limit"]);
				echo json_encode($data);			
			}
			break;
		case "getNextDocumentIdsByJob": 
			if(isset($_POST["job_id"])& isset($_POST["dataset_id"])& isset($_POST["limit"]))
			{
				$data = getNextDocumentIdsByJob($_POST["job_id"],$_POST["dataset_id"],$_POST["limit"]);
				echo json_encode($data);			
			}
			break;
		case "getUserIdByEmailOrInsert":
			if(isset($_POST["email"]) && isset($_POST["password"]))
			{
				$data =getUserIdByEmailOrInsert($_POST["email"], $_POST["password"]);
				echo json_encode($data);	
			}
			break;
		case "getUserIdByUsernameAndPassword":
			if(isset($_POST["username"]) && isset($_POST["password"]))
			{
				$data =getUserIdByUsernameAndPassword($_POST["username"], $_POST["password"]);
				echo json_encode($data);	
			}
			break;
		case "getJobIdOrInsert":
		if(isset($_POST["dataset_id"]) && isset($_POST["user_id"]) && isset($_POST["task_id"]))
			{
				$data =getJobIdOrInsert($_POST["dataset_id"], $_POST["task_id"], $_POST["user_id"]);
				echo json_encode($data);	
			}
			break;
		case "getIntrotext":
			if(isset($_POST["dataset_id"]) && isset($_POST["task_id"]))
			{
				echo json_encode(getIntrotext($_POST["dataset_id"], $_POST["task_id"]));	

			}
			break;		
		case "getUserIdByEmail":
			if(isset($_POST["email"]))
			{
				$data =getUserIdByEmail($_POST["email"]);
				echo json_encode($data);	
			}
			break;
		case "getTokensByDocumentId": 
			if(isset($_POST["document_id"])& isset($_POST["job_id"]))
			{
				$data = getTokensByDocumentId($_POST["document_id"],$_POST["job_id"]);
				echo json_encode($data);			
			}
			break;
		case "getProgressForJob":
			if(isset($_POST["job_id"]))
			{
				echo json_encode(getProgressForJob($_POST["job_id"]));
			}
			break;
		case "getLabelsByTaskId": 
			if(isset($_POST["task_id"]))
			{
				$data = getLabelsByTaskId($_POST["task_id"]);
				echo json_encode($data);			
			}
			break;
		case "insertJudgement": 
			{
				if(isset($_POST["unit_id"])& isset($_POST["label_id"])& isset($_POST["job_id"]))
				{
					$pos = (isset($_POST["pos"])) ? $_POST["pos"] : "";
					echo insertJudgement($_POST["unit_id"],$_POST["label_id"],$_POST["job_id"],$pos);
				}
				else
					echo "else";
			}
			break;
		case "getAllUserProgress": 
			
				$data = getAllUserProgress();
				echo json_encode($data);			
			break;
	}
}
?>