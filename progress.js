
var activeDataset=8;
window.setInterval(function(){
 getProgressForDataset(activeDataset);}, 1000);

var fillstyle =[];
fillstyle[1]="#FF8800";
fillstyle[2]="#99FF00";
fillstyle[0]="#3D4C53";

function getNumberOfAnnotatedTweets(dataset_id)
{
$.ajax({
          type: "POST",
		  data: { method:"getNumberOfAnnotatedDocuments", dataset_id:dataset_id},
		  url: "getData.php",
           cache: false,
		  success: receiveNumberOfAnnotatedTweets,
		  error:  onError});		 
	
}
function receiveNumberOfAnnotatedTweets(jsonData)
{
var result = JSON.parse(jsonData);
document.getElementById("completedTweets").innerHTML = result.count;

}
function deleteAllJudgements()
{
var r = confirm("Are you sure you want to delete ALL judgements for this dataset?");
if (r == true) {
    $.ajax({
          type: "POST",
		  data: { method:"deleteJudgmentsDataset", dataset_id:activeDataset},
		  url: "getData.php",
           cache: false,
		  success: judgementsDeleted,
		  error:  onError});	

} 
}
function judgementsDeleted()
{
	window.alert("Judgements deleted");
}
function updateCanvas(progress){
    var canvas = document.getElementById("myCanvas");
    var context = canvas.getContext("2d");
	var radius = 7;
	var linewidth =1;
    var centerX = 1.5*radius;
    var centerY = 1.5*radius;
	var i=0;
	var rowsize = 50;
	var rowcount = 0;
		for (i = 0; i < progress.length; i++){
	
				if(i%rowsize == 0)
				{
					rowcount++;
				}
				var color=fillstyle[0];
				if(progress[i])
				{
					if(progress[i].count>0 && progress[i].count <3)
						color=fillstyle[progress[i].count];
					if(progress[i].count>2)
						color=fillstyle[2];
				}
					
				drawCircle(context, centerX+(i%rowsize)*radius*2.5, centerY+rowcount*radius*2.5, radius, color)
				
   
	}
};
function onError(xhr, desc, err) {
          console.log(xhr + "\n" + err);
         }
function drawCircle(context, centerX, centerY, radius, fillstyle)
{
   context.beginPath();
    context.arc(centerX, centerY, radius, 0, 2 * Math.PI, false);
    context.fillStyle = fillstyle;
    context.fill();
	context.lineWidth = 1;
    context.strokeStyle = fillstyle;
    context.stroke();
}
function getProgressForDataset(dataset_id)
{
	getNumberOfAnnotatedTweets(dataset_id);
	$.ajax({
          type: "POST",
		  data: { method:"getDatasetProgress", dataset_id:dataset_id},
		  url: "getData.php",
           cache: false,
		  success: receiveProgress,
		  error:  onError});		 
}
function receiveProgress(jsonData)
{
	var progress = JSON.parse(jsonData);
	//document.write(jsonData);
	updateCanvas(progress);
}