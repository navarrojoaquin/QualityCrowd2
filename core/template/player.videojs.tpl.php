<?php

$uid = uniqid();

if (strlen($rebufferingsimulation) == 0) {
	$rebufferingsimulation = 'undefined';
}

?>

<div id="loading_message_<?= $uid ?>">
	The test is loading... Please wait.
</div>
<!-- We need to use uniqid because this template can be included several times in the same webpage -->
<div id="test_box_<?= $uid ?>" class="test_box" style="visibility:hidden">
	<video id="video_div_<?= $uid ?>" class="video-js vjs-default-skin vjs-big-play-centered" 
	       controls preload="auto" poster="/QualityCrowd2/core/files/img/Black-background.gif" 
	       width="<?= $width; ?>" height="<?= $height; ?>"
	       data-setup='{ "autoplay": false }'>
	</video>
</div>


<script type="text/javascript">
	videojs('video_div_<?= $uid ?>').ready(function() {
		var myPlayer = this;
		var lastRebufferingTime = null;
		var initialBuffering = true;
		var buffering = false;
		// Set handlers for the events we are interested in
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
			//myPlayer.controls(false);
			disableUI();
			buffering = false;
			if (initialBuffering) {
				initialBuffering = false;
				/* Force a timeupdate event to simulate initial buffering (rebuffering t=0).
				   That's not necessary on Firefox, but it is on Chrome because it fires a timeupdate
				   event before playing the video.
				   See https://code.google.com/p/chromium/issues/detail?id=223476
				   We let some time (50ms) to let the player start */
				setTimeout(function(){myPlayer.trigger("timeupdate");}, 50);
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
		// We load the video now to ensure that the event callbacks are set (prevent errors on mobile devices)
		// Videos are supossed to be encoded in .mp4 and in .webm
		var videoMp4File = "<?= $file; ?>";
		var videoWebmFile = videoMp4File.replace("mp4", "webm");
		myPlayer.src([
			{ type: "video/mp4", src: videoMp4File },
			{ type: "video/webm", src: videoWebmFile }		
		]);
		myPlayer.load();
		function startBuffering() {
			myPlayer.trigger("waiting");
			//myPlayer.controls(false); // Hide the controls while simulating buffering
			myPlayer.pause();
		}
		function stopBuffering() {
			myPlayer.play();
			// Show the controls
			//myPlayer.controls(true);
			//myPlayer.controlBar.fadeOut();
		}
		/* We disable the UI to allow the controlBar to be seen but not clickable */
		function disableUI() {
			myPlayer.controlBar.playToggle.off("mousedown");
			myPlayer.controlBar.playToggle.off("touchstart");
			myPlayer.controlBar.playToggle.off("click");
			myPlayer.controlBar.progressControl.seekBar.off("mousedown");
			myPlayer.controlBar.progressControl.seekBar.off("touchstart");
			myPlayer.controlBar.progressControl.seekBar.off("click");
			myPlayer.tech.removeControlsListeners(); // This only works with the dev version of videojs
		}
		var simulatedRebufferings = <?= $rebufferingsimulation ?>;
		function simulateRebuffering() {
			/* Google Chrome triggers a timeupdate=0 event when the video loads, so if the video is paused
			   there is nothing to do here */
			if (myPlayer.paused()) return;
			var t_rebuffering = 0;
			var rebufferings_to_delete = [];
			var t = myPlayer.currentTime();
			var threshold = 2;
			for (var i in simulatedRebufferings) {
				if (t - i < threshold) {
					t_rebuffering += simulatedRebufferings[i];
					rebufferings_to_delete.push(i);
				}
			}
			for (var i=0; i < rebufferings_to_delete.length; i++) {
				delete simulatedRebufferings[rebufferings_to_delete[i]];
			}
			if (t_rebuffering > 0) {
				console.log("Simulating rebuffering");
				
				setTimeout(stopBuffering, t_rebuffering * 1000);
				startBuffering();
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
	});
</script>