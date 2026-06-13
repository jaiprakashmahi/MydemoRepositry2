
(function(){
  function markActive(){
    var current=(location.pathname.split('/').pop()||'index.php').toLowerCase();
    document.querySelectorAll('.dlabnav a[href]').forEach(function(a){
      var href=(a.getAttribute('href')||'').split('?')[0].toLowerCase();
      if(href===current){
        a.classList.add('active');
        var li=a.closest('li'); if(li) li.classList.add('mm-active');
        var parent=a.closest('ul');
        if(parent && parent.previousElementSibling){
          parent.style.display='block';
          parent.previousElementSibling.classList.add('active');
          var pli=parent.previousElementSibling.closest('li'); if(pli) pli.classList.add('mm-active');
        }
      }
    });
  }
  if(document.readyState==='loading') document.addEventListener('DOMContentLoaded',markActive); else markActive();
})();
