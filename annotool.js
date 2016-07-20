var activeLabel = 0;
		var activeJob;
		var activeTask;
		var activeUser = 0;
		var activeStep =1;
		var activeDataset;

		var activeTaskMaxStep;
		var activeJobLimit=20;
		var activeTextId =0;
		var activeTaskShowReset =0;
		var activeTaskLimit=20;
		var activeTaskType;
		var tokenList = [];
		var labelList =[];
	    var showNavButtons = true;
		var activeSentence=0;
		var buttonNext;
		var labelStyle = [];
		var defaultLabel;
		var unkownLabel;
		bootstrapButtons = ["btn btn-primary", "btn btn-info", "btn btn-warning", "btn btn-danger", "btn btn-turkis", "btn btn-success", "btn btn-purple"];

		var noLabel = ["PUNCT", "OP", "CP"];
		
		
		function login()
		{
			var main_div = document.getElementById("main_div");
			var login_div = document.getElementById("login_div");
			var select_task_div = document.getElementById("select_task_div");
			if(activeUser == 0)
			{
				main_div.style.display = 'none';
				$('#select_task_div').hide();

				login_div.style.display = 'block';          // Show
			}
		}
		function resetView()
		{
			$('#main_div').hide();
			$('#login_div').hide();
			$('#select_task_div').hide();

			$('#thankyou_div').hide();
			$('#token_div').hide();
			$('#text_div').hide();
			$('#label_div').hide();
			$('#button_div').hide();
			$('#task_div').hide();
			$('#overview_div').hide();
			$('#status_div').hide();

		}
		function start()
		{
			resetView();
			
			$('#main_div').show();

		getIntroText();
			
			if(activeTaskType=="TEXT")
			getSentencesForUser(activeJob);
			if(activeTaskType=="TOKEN")
			getSentencesTokensForUser(activeJob);
		}
		function getIntroText()
		{
			$.ajax({
			  type: "POST",
			  data: { method:"getIntrotext", dataset_id: activeDataset, task_id:activeTask},
			  url: "getData.php",
			  cache: false,
			  success: receiveIntroText,
			  error:  onError});	
		}
		function receiveIntroText(jsonData)
		{	
			var response = JSON.parse(jsonData);
			var introtext = response.introtext;
			
			var task_div = document.getElementById("task_div")
			task_div.innerHTML=introtext;
			
			$('#task_div').show();

			
		}
		function initButtons(activeTask)
		{
		
			var button_div = document.getElementById("button_div")
			button_div.innerHTML="";
			if(activeTaskType=="TOKEN"){
			
						$('#button_div').show();

			var element = document.createElement("button");
			element.innerHTML ="<";
			element.id="previousSentence";
			element.setAttribute("class", "btn btn-default btn-spacing-both");
			element.addEventListener('click', previousListener, false);	
			//button_div.appendChild(element);
			
			if(activeTaskShowReset==1)
			{
			var element = document.createElement("button");
			element.innerHTML ="Hapus";
			element.id="reset";
			element.setAttribute("class", "btn btn-default btn-spacing-both");
			element.addEventListener('click', resetListener, false);	
			button_div.appendChild(element);
			}
		/*	var element = document.createElement("button");
			element.innerHTML ="Skip";
			element.id="skipSentence";
			element.setAttribute("class", "btn btn-default btn-space");
			element.addEventListener('click', skipListener, false);	
			button_div.appendChild(element);*/
			
			var element = document.createElement("button");
			element.innerHTML =">";
			element.id="nextSentence";
			element.setAttribute("class", "btn btn-default btn-spacing-both");
			element.addEventListener('click', nextListener, false);	
			button_div.appendChild(element);
			
			
		
			/*var element = document.createElement("button");
			element.innerHTML ="END";
			element.id="endButton";
			element.setAttribute("class", "btn btn-default btn-spacing-both");
			element.addEventListener('click', stopAnnotation, false);	
			button_div.appendChild(element);
			*/
			updateButtons();

			
			}
			
		}
		
		function loadSelectTaskDiv()
		{

				$.ajax({
          type: "POST",
		  data: { method:"getJobsByUser", user_id:activeUser},
		  url: "getData.php",
		  cache: false,
		  success: receiveJobsForUser,
		  error:  onError});		
		}
		function getProgressForJob()
		{
		  $.ajax({
          type: "POST",
		  data: { method:"getProgressForJob", job_id:activeJob},
		  url: "getData.php",
		  cache: false,
		  success: receiveProgressForJob,
		  error:  onError});	
		}
		function receiveProgressForJob(jsonData)
		{
			var result =  JSON.parse(jsonData);
			var recordsDone = result.count;
			$('#status_div').show();
			var task_div = document.getElementById("progress")
			if(recordsDone>0)
			task_div.innerHTML="You already tagged " + recordsDone + " from " + activeTaskLimit + " records";
		
		}
		function receiveJobsForUser(jsonData)
		{
			
			resetView();
			$('#select_task_div').show();


			var jobs = JSON.parse(jsonData);
			task_select_div.innerHTML="";
			task_select_div = document.getElementById("task_select_div");
			
			for(j in jobs)
			{
				var element = document.createElement("button");
				
				element.innerHTML =jobs[j].task_name + " " + jobs[j].dataset_name;
				element.id=jobs[j].job_id;
				element.task_type=jobs[j].task_type;
				element.task_id=jobs[j].task_id;
				element.dataset_id=jobs[j].dataset_id;
				element.task_maxStep = jobs[j].task_maxStep;
				element.task_showReset = jobs[j].showReset;
				element.task_limit = jobs[j].task_limit;
				element.setAttribute("class", "btn btn-default btn-space");
				element.addEventListener('click', taskSelectListener, false);	
				task_select_div.appendChild(element);
				task_select_div.appendChild(document.createElement("br"));
				task_select_div.appendChild(document.createElement("br"));

			}
		
		}
		
		function taskSelectListener(evt)
		{
			job = evt.target;
			activeJob = job.id;
			activeDataset = job.dataset_id; 
			activeTask = job.task_id;
			activeTaskType = job.task_type;
			activeTaskShowReset=job.task_showReset;
			activeTaskMaxStep = job.task_maxStep;
			activeTaskLimit = job.task_limit;
			resetView();
			$('#overview_div').show();

			getProgressForJob();
		}
		function loadNewSentence()
		{
			var nextSentence = sentences[activeSentence];
			
			if(nextSentence){
				if(activeTaskType == "TOKEN")
					initTokens(nextSentence, activeJob);
				//initTokensByDocument(nextSentence.document_id, activeJob);
				if(activeTaskType == "TEXT")
					initText(nextSentence, activeJob);


				updateLabels();
			}
			else
			{
				stopAnnotation();
			}
		}
		function getSentencesTokensForUser(job_id)
		{

			activeSentence=0;
			$.ajax({
          type: "POST",
		  data: { method:"getNextTokensByTokenJob", job_id:job_id, dataset_id: activeDataset, limit:activeJobLimit},
		  url: "getData.php",
         cache: false,
		  success: receiveSentencesForUser,
		  error:  onError});	
		  
		}
	/*	function getSentencesTokensForUser(job_id)
		{

			activeSentence=0;
			$.ajax({
          type: "POST",
		  data: { method:"getNextDocumentIdsByTokenJob", job_id:job_id, limit:activeJobLimit},
		  url: "getData.php",
         cache: false,
		  success: receiveSentencesForUser,
		  error:  onError});	
		  
		}*/
		function getSentencesForUser(job_id)
		{
			$('#task_div').show();
			activeSentence=0;
			$.ajax({
          type: "POST",
		  data: { method:"getNextDocumentIdsByJob", job_id:job_id, dataset_id: activeDataset, limit:activeJobLimit},
		  url: "getData.php",
         cache: false,
		  success: receiveSentencesForUser,
		  error:  onError});	
		  
		}
		function receiveSentencesForUser(jsonData)
		{
			  sentences = JSON.parse(jsonData);
			  if(sentences.length > 0)
				{
					initButtons(activeTask);
					initLabels(activeTask);
					initProgressbar(activeJobLimit);
					loadNewSentence();
				}
				else
				{
					document.getElementById("info_div").innerHTML = "It seems you annotated all the data! Thank you! For now, there is no more data to annotate!";
								$('#info_div').show();

				}
			  
		}
		function 	initProgressbar(limit)
		{
			var progressbar = document.getElementById("progressbar");
			progressbar.setAttribute("style", "width:0%");

		}
		function increaseProgressBar()
		{
			progressbar.setAttribute("style", "width:"+((activeSentence+1)/activeJobLimit)*100+"%");
		}
		function initText(nextSentence, job_id)
		{
				$('#text_div').show();

			activeTextId= nextSentence.unit_id;
	var tokendiv = document.getElementById("text_div");
	tokendiv.innerHTML=nextSentence.text;

}
		function initTokens(nextSentence, job_id)
		{
			var tokens = nextSentence.tokens;
			showTokens(tokens);	
		}
					
			
		
		function initTokensByDocument(document_id, job_id)
		{
	
			$.ajax({
          type: "POST",
		  data: { method:"getTokensByDocumentId", document_id: document_id, job_id:job_id},
		  url: "getData.php",
           cache: false,
		  success: receiveTokens,
		  error:  onError});		 
		}
		function showTokens(tokens)
		{
		
		$('#token_div').show();

		var tokenbuttons = ""; 
		
		var tokendiv = document.getElementById("token_div");
		tokendiv.innerHTML=""; //delete old buttons
		tokenList = new Array();

		for (t in tokens)
		{
			
			var group = document.createElement("div");
			group.setAttribute("class", "btn-group-vertical");
			var element = document.createElement("button");
			element.innerHTML =tokens[t].token;
			element.id=tokens[t].unit_id;
			element.assignedLabel="";
			if(tokens[t].label_id != null)
			{
				element.assignedLabel = tokens[t].label_id;
				element.setAttribute("class", labelStyle[tokens[t].label_id]);
			}
			else 
				element.setAttribute("class", "btn btn-default");

			
			if($.inArray(tokens[t].pos_tag, noLabel) > -1)
			element.disabled=true;
			
			element.addEventListener('click', tokenListener, false);	
			tokenList.push(element);
			
			group.appendChild(element);
			if(tokens[t].pos_tag!="")
			{
				var postag = document.createElement("button");
				postag.innerHTML =tokens[t].pos_tag;
				postag.setAttribute("class", "btn btn-default btn-xs disabled");
				group.appendChild(postag);
			} 
			var space = document.createElement("div");
			space.setAttribute("class", "space");

			tokendiv.appendChild(group);
		}
		}
		function receiveTokens(jsonData)
		{
			var tokens = JSON.parse(jsonData);
			showTokens(tokens);
		}
		function previousListener(evt)
		{
			saveJudgement();
			if(activeStep>1)
			{
				activeStep--;
				updateLabels();
			}
			else
			{
				activeSentence--;
				activeStep = activeTaskMaxStep;
				if(activeSentence >= 0)
					loadNewSentence();
			}
			updateButtons();
		}
		function allTokensAreDefault()
		{
			var allAreDefault = true;

			if(activeTaskType == "TOKEN")
			{
			
				for(i in tokenList)
				{			
					var i_token = tokenList[i]; 
					
					if(i_token.assignedLabel!="")
						allAreDefault =false;
				}
				return allAreDefault;
			}
				else return false; 
		
		}
		function nextListener(evt)
		{
			if(allTokensAreDefault())
				labelAllTokens(unkownLabel);
				
			saveJudgement();
			increaseProgressBar();
			if(activeStep < activeTaskMaxStep)
			{
				activeStep++;
				updateLabels();
			}
			else
			{
				if(activeSentence < sentences.length)
				{
					activeSentence++;
					activeStep=1;
					loadNewSentence();					
				}
				else
				 stopAnnotation();
			}
			updateButtons();
		}
		function updateButtons()
		{
			if(activeSentence +1 == sentences.length && activeStep == activeTaskMaxStep)
			{$('#nextSentence').visible();}else
			{$('#nextSentence').visible();$('#skipSentence').visible();  } 
			if(activeSentence  == 0 && activeStep ==1 ) $('#previousSentence').invisible(); else $('#previousSentence').visible(); 
		}
		function saveJudgement()
		{
			var unit_id;
			var label_id;
			var position_field;
			
			if(activeTaskType == "TOKEN")
			{
				var position = "";
				for(i in tokenList)
				{			
					var i_token = tokenList[i]; 
					
					if(i_token.assignedLabel=="")
					//if(typeof(i_token.assignedLabel) == -1)
					{
						i_token.assignedLabel = defaultLabel;
					}
						
					if(i == 0)
					position = "B";
					else
					{
						if(tokenList[i-1].assignedLabel == i_token.assignedLabel)
							position = "I";
						else
							position = "B";
					}
					
						
					unit_id = i_token.id;
					label_id = i_token.assignedLabel;
					position_field = position;
					res = insertJudgement(unit_id, label_id, activeJob, position_field);

				}
			}
			if(activeTaskType == "TEXT")
			{
				unit_id = activeTextId;
				label_id = activeLabel;
				position_field = ""; 
				res = insertJudgement(unit_id, label_id, activeJob, position_field);

			}


		}
	
		function skipListener(evt)
		{
			activeSentence++;

			if(activeSentence < sentences.length)
			{
				loadNewSentence();
			}
			else
			stopAnnotation();
		}
		function resetListener(evt)
		{
			for(i in tokenList)
			{
				tokenList[i].assignedLabel = defaultLabel;
				tokenList[i].setAttribute("class", labelStyle[defaultLabel]);
			}

			
		}
		function stopAnnotation()
		{
			saveJudgement();
			
			document.getElementById("number_docs").innerHTML = activeJobLimit;
			resetView();
			$('#thankyou_div').show();
			getProgressForJob();

		}
		function tokenListener(evt)
		{
			evt.target.assignedLabel = activeLabel;
			evt.target.setAttribute("class", labelStyle[activeLabel]);
		}
		function labelListener(evt)
		{
		
			if(activeTaskType == "TOKEN")
			activeLabel = evt.target.id;
			if(activeTaskType == "TEXT")
			{
				activeLabel = evt.target.id;
				nextListener(evt);
			}

		}
		function labelAllListener(evt)
		{
		
		activeLabel = evt.target.id;
		if(activeTaskType == "TOKEN")
		{
			for(i in tokenList)
			{
				tokenList[i].assignedLabel = evt.target.id;
				//tokenList[i].setAttribute("class", labelStyle[evt.target.id]);
			}
			nextListener(evt);
		}
		}
		function labelAllTokens(label)
		{
		
		if(activeTaskType == "TOKEN")
		{
			for(i in tokenList)
			{
				tokenList[i].assignedLabel = label;
			}
		}
		
		}
		function updateLabels()
		{
					$('#label_div').show();

			var labeldiv = document.getElementById("label_div");
			labeldiv.innerHTML="";
				var label_all_div = document.getElementById("label_all_div");
			label_all_div.innerHTML="";
			var i =0;
		for (t in labelList)
		{

			if(labelList[t].step == activeStep || labelList[t].step == 0)
			{
				if(i == 0)
				{	activeLabel = labelList[t].label_id; i++;}
					
				var element = document.createElement("button");
				element.innerHTML =labelList[t].label;
				element.myParam = labelList[t].label;
				if(labelList[t].is_skip == 1)
				{
					unkownLabel = labelList[t].label_id;
				}
				
				if(labelList[t].is_default == 1)
				{
					labelStyle[labelList[t].label_id] = "btn btn-default";
					defaultLabel = labelList[t].label_id;
				}
				else
					labelStyle[labelList[t].label_id] = labelList[t].style_class;
				element.setAttribute("class", "btn-spacing-both " +labelStyle[labelList[t].label_id]);
				element.id = labelList[t].label_id;
				element.addEventListener('click', labelListener, false);	
				if(labelList[t].is_computed==0)
				labeldiv.appendChild(element);
				if(labelList[t].mark_all==1)
				{
					
					element.addEventListener('click', labelAllListener, false);	
					label_all_div.appendChild(element);
				
				}
			}
		}
		}
		function initLabels(task_id)
		{
			activeStep=1;

		  var labeldata = $.ajax({
          type: "POST",
		  data: { method:"getLabelsByTaskId", task_id:task_id},
		   url: "getData.php",
		  cache: false,
		  success: receiveLabels,
		  error:  onError});		  
				
		}
		function receiveLabels(json)
		{
			labelList = JSON.parse(json);
			updateLabels();
		}
		 function onError(xhr, desc, err) {
          console.log(xhr + "\n" + err);
         }
		
		function insertJudgement(unit_id, label_id, job_id, pos)
		{		//	 console.log("try to insert Judgement");
			$.ajax({
			  type: "POST",
			  data: { method:"insertJudgement", unit_id: unit_id, label_id: label_id, job_id: job_id, pos: pos},
			  url: "getData.php",
			 cache: false,
			success: receiveInsertJudgement,
			 error:  onError});	
		}
		function receiveInsertJudgement(jsonData)
		{		//	 console.log("receive InsertJudgement" + jsonData);
			
		}
		/*function tryToLogin()
		{
			var emailInput = document.getElementById("username").value;
			var passwordInput = document.getElementById("password").value;
			
			$.ajax({
			  type: "POST",
			  data: { method:"getUserIdByEmailOrInsert", email: emailInput, password: passwordInput},
			  url: "getData.php",
			  cache: false,
			  success: getUserIdByEmailOrInsert,
			  error:  onError});	

			

		}*/
		function tryToLogin()
		{
			var userInput = document.getElementById("username").value;
			var passwordInput = document.getElementById("password").value;
			
			//if(validateEmail(emailInput))
			if(userInput.length >0)
			{
				$('#userError').hide();
			$.ajax({
			  type: "POST",
			  data: { method:"getUserIdByUsernameAndPassword", username: userInput, password: passwordInput},
			  url: "getData.php",
			  cache: false,
			  success: getUserIdByEmailOrInsert,
			  error:  onError});	
			}
			else
			{
				$('#userError').show();
			}
			

		}
		function getJobIdOrInsert(user_id, task_id, dataset_id)
		{
			  $.ajax({
			  type: "POST",
			  data: { method:"getJobIdOrInsert", dataset_id: dataset_id, task_id:task_id, user_id:user_id},
			  url: "getData.php",
			  cache: false,
			  success: receiveJobId,
			  error:  onError});	
		}
		function receiveJobId(jsonData)
		{
			var job = JSON.parse(jsonData);
			activeJob = job.job_id;
			start();
		}
		function showContact()
		{
					$('#contact').show();

			 document.getElementById('contact').scrollIntoView();
			

		}
		function saveFeedback()
		{
		
			var feedback_use = "";
			var selected = $("input[type='radio'][name='feedback_use']:checked");
			if (selected.length > 0) {
				feedback_use = selected.val();
			}
			var feedback_like = "";
			var selected = $("input[type='radio'][name='feedback_like']:checked");
			if (selected.length > 0) {
				feedback_like = selected.val();
			}
			
			var feedback_text = $("#feedback_text").val();
			
				$.ajax({
			  type: "POST",
			  data: { method:"insertFeedback", job_id: activeJob, feedback_like:feedback_like, feedback_use: feedback_use, feedback_text:feedback_text},
			  url: "getData.php",
			 cache: false,
			success: showThankyouFeedback,
			 error:  onError});			
		}
		function showThankyouFeedback()
		{
				document.getElementById("feedbackForm").reset();

				$('#thankyou_feedback').show();
		}
		function showFeedback()
		{
				$('#thankyou_feedback').hide();

					$('#feedback').show();

			 document.getElementById('feedback').scrollIntoView();
			

		}
		function hideContact()
		{
			$('#contact').hide();
			document.getElementById('main_div').scrollIntoView();

		}
		function hideFeedback()
		{
			$('#feedback').hide();
			$('#thankyou_feedback').hide();

			document.getElementById('main_div').scrollIntoView();

		}
		function getUserIdByEmailOrInsert(jsonData)
		{
			var user = JSON.parse(jsonData);
			activeUser = user.user_id;
			if(activeUser > 0) 
			{
			loadSelectTaskDiv();
			//	getJobIdOrInsert(activeUser, activeTask, activeDataset);
				
			}
			else 
			{
				$('#userError').show();
			}
		}
		
		(function($) {
    $.fn.invisible = function() {
        return this.each(function() {
            $(this).css("visibility", "hidden");
        });
    };
    $.fn.visible = function() {
        return this.each(function() {
            $(this).css("visibility", "visible");
        });
    };
}(jQuery));
function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}