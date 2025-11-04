function toggleView(view) {
    document.getElementById('media-view').classList.add('hidden');
    document.getElementById('loans-view').classList.add('hidden');
    document.getElementById(view + '-view').classList.remove('hidden');
}