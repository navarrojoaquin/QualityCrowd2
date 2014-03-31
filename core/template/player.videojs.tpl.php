<?php

$uid = uniqid();

if (strlen($rebufferingsimulation) == 0) {
	$rebufferingsimulation = 'undefined';
}

?>

<div id="loading_message_<?= $uid ?>">
		The test is loading... Please wait :)
</div>
<!-- We need to use uniqid because this template can be included several times in the same webpage -->
<div id="test_box_<?= $uid ?>" style="visibility:hidden">
	<video id="example_video_1_<?= $uid ?>" class="video-js vjs-default-skin vjs-big-play-centered" 
	       controls preload="auto" poster="http://video-js.zencoder.com/oceans-clip.png" 
	       width="<?= $width; ?>" height="<?= $height; ?>"
	       data-setup='{ "controls": true, "autoplay": false, "preload": "auto" }'>
 		<!--<source src="/QualityCrowd2/media/bigbuck.mp4" type='video/mp4; codecs="avc1.42E01E, mp4a.40.2"' />-->
 		<source src="<?= $file; ?>" type='video/mp4'/>
	</video>
</div>


<script type="text/javascript">
/* We use a function to create a new scope. This is useful when this template is included several times in the same webpage. */
function initPlayer<?= $uid ?>() {
	var myPlayer = videojs('example_video_1_<?= $uid ?>');
	var lastRebufferingTime = null;
	var initialBuffering = true;
	var buffering = false;
	myPlayer.on("waiting", function() {
		if (initialBuffering) {
			console.log("Initial Buffering");
			return;
		}
		console.log("Waiting");
		buffering = true;
		
	});
	myPlayer.on("play", function() {
		console.log("Playing");
		buffering = false;
		if (initialBuffering) {
			initialBuffering = false;
		}
		if (lastRebufferingTime != null) {
			var duration = new Date() - lastRebufferingTime;
			console.log("Rebuffering duration=" + duration/1000 + " s");
			lastRebufferingTime = null;
		}
	});
	myPlayer.on("pause", function() {
		console.log("Paused");
		if (buffering) {
			console.log("Buffering " + myPlayer.paused());
			if (lastRebufferingTime == null) {
				console.log("New rebuffering event");
				lastRebufferingTime = new Date();
			}
		}
		
	});
	myPlayer.on("canplaythrough", function() {
		var divLoadingMessage = document.getElementById("loading_message_<?= $uid ?>");
		var divTest = document.getElementById("test_box_<?= $uid ?>");
		divTest.style.visibility = 'visible';
		divLoadingMessage.style.visibility = 'hidden';
	});
	function startBuffering() {
		myPlayer.trigger("waiting");
		myPlayer.pause();
	}
	function stopBuffering() {
		myPlayer.play();
	}
	var simulatedRebufferings = <?= $rebufferingsimulation ?>;
	function simulateRebuffering() {
		var t_rebuffering = 0;
		var rebufferings_to_delete = [];
		var t = myPlayer.currentTime();
		for (var i in simulatedRebufferings) {
			if (t > i) {
				t_rebuffering += simulatedRebufferings[i];
				rebufferings_to_delete.push(i);
			}
		}
		for (var i=0; i < rebufferings_to_delete.length; i++) {
			delete simulatedRebufferings[rebufferings_to_delete[i]];
		}
		if (t_rebuffering > 0) {
			console.log("Simulating rebuffering");
			startBuffering();
			setTimeout(stopBuffering, t_rebuffering * 1000);
		}			
	}
	if (simulatedRebufferings !== undefined) {
		myPlayer.on("timeupdate", simulateRebuffering);
	}
	/* It is required to call onVideoComplete when the video ends.
	   If not, an error message will be shown when the user clicks the 'next' button. */
	myPlayer.on("ended", function() {
		if (typeof(onVideoComplete) == 'function') {
					onVideoComplete('<?= $filename ?>');
		}
	});
}
initPlayer<?= $uid ?>();
</script>