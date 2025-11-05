function toggleView(view) {
    sessionStorage.setItem('currentView', view + '-view');
    document.getElementById('media-view').classList.add('hidden');
    document.getElementById('loans-view').classList.add('hidden');
    document.getElementById(view + '-view').classList.remove('hidden');
}

document.addEventListener('DOMContentLoaded', function() {
    const savedView = sessionStorage.getItem('currentView') || 'media-view';
    toggleView(savedView.replace('-view', ''));

    document.getElementById('media-view').addEventListener('click', function() {
        toggleView('media');
    });

    document.getElementById('loans-view').addEventListener('click', function() {
        toggleView('loans');
    });
})