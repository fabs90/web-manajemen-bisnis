function initYouTubePlayer() {
    const videoEl = document.getElementById("video-player");
    if (!videoEl || typeof YT === "undefined" || !YT.Player) {
        return;
    }

    new YT.Player("video-player", {
        events: {
            onReady: onPlayerReady,
            onStateChange: onPlayerStateChange,
        },
    });
}

function onPlayerReady(event) {
    event.target.playVideo();
}

function onPlayerStateChange(event) {
    if (typeof YT !== "undefined" && event.data === YT.PlayerState.ENDED) {
        event.target.stopVideo();
    }
}

if (typeof YT !== "undefined" && YT.Player) {
    initYouTubePlayer();
} else {
    window.onYouTubeIframeAPIReady = initYouTubePlayer;
}
