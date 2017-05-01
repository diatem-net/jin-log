function debugOpenClose(id) {
  content = document.getElementById('dump_segment_content_'+id);
  if (content.style.display == '') {
    content.style.display = 'none';
  } else {
    content.style.display = '';
  }
}