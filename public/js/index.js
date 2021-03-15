(() => {

const baseUrl = window.location.origin + '/';

Array.prototype.map.call(
    document.getElementsByTagName('nav')[0].getElementsByTagName('p'),
    p => p.addEventListener(
        'click',
        e => window.location.href =  baseUrl + e.target.getAttribute('data-url')
    )
);

})();