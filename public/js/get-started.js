const player = new YT.Player("video-player", {
    events: {
        onReady: onPlayerReady,
        onStateChange: onPlayerStateChange,
    },
});

function onPlayerReady(event) {
    event.target.playVideo();
}

function onPlayerStateChange(event) {
    if (event.data === YT.PlayerState.ENDED) {
        // Handle video end event
        event.target.stopVideo();
    }
}
