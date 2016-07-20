<html>
<?php
    
	error_reporting( E_ALL ); 
    ini_set('display_errors', 'on');
    include 'functions.php';
	include 'getData.php';
	
	require_once('simple_html_dom.php');

?>   
<script type="text/javascript" src="js/vendor/jquery.min.js"></script>

	   <script type='text/javascript'>
		
	
		  var jsonData = $.ajax({
          type: "POST",
		  data: { method:"getAllUserProgress"},
		  url: "getData.php",
          dataType:"json",
          async: false
          }).responseText;
		 data = JSON.parse(jsonData);
		  document.write("<table border=1>");
		   document.write("<tr><td>Userid</td><td>Username</td><td>Task</td><td>Dataset</td><td>Labelled documents</td></tr>");

		  for(u in data)
			{
				user = data[u];
				
					  document.write("<tr><td>" +user.user_id + "</td><td>" + user.username + "</td><td>" + user.task + "</td><td>" + user.dataset + "</td><td>"+ user.documents+ "</td></tr>");

			}
		 		  document.write("</table>");

	</script>
	 </head>
  <body>
    <div id="table_div"></div>
  </body>
</html>
