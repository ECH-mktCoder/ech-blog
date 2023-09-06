var pageInitial = true;

jQuery(document).ready(function(){
    
    var currentPage = jQuery(".ech_blog_pagination").data("current-page");
    paginationGenerate(currentPage);
});


function paginationGenerate(page){

    var maxPage = jQuery(".ech_blog_pagination").data("max-page");
    //var pagination = '<ul><li><a href="#" onclick="paginationGenerate(checkPrevious(' + page + ')) ">&laquo;</a></li>';
    //var pagination = '<ul><li><a href="#" onclick="">&laquo;</a></li>';
    var pagination = '<ul><li>&laquo;</li>';

    /* Page Range Calculation */
    var range = pageRange(page, maxPage);
    var start = range.start;
    var end = range.end;
    //console.log('start: '+start + ' | end: ' + end);
    
    for (var page_id = start; page_id <= end; page_id++) {
        if (page_id != page) pagination += '<li><a href="#" onclick="paginationGenerate(' + page_id + ')">' + page_id + '</a></li>';
        else pagination += '<li class="active"><span>' + page_id + '</span></li>';
    }
    //pagination += '<li><a href="#" onclick="paginationGenerate(checkNext(' + page + ',' + maxPage + '))">&raquo;</a></li></ul>';
    //pagination += '<li><a href="#" onclick="">&raquo;</a></li></ul>';
    pagination += '<li>&raquo;</li></ul>';

    if(!pageInitial) {        
        load_more_posts(page);
    }
    
    /* Appending Pagination */
    jQuery('.ech_blog_pagination ul').remove();
    jQuery('.ech_blog_pagination').append(pagination);
    
    // change data-current-page value
    jQuery(".ech_blog_pagination").data("current-page", page);
    jQuery(".ech_blog_pagination").attr("data-current-page", page);

    pageInitial = false;
}




/* Pagination Navigation */
function checkPrevious(id) {
    if (id > 1) {
        return (id - 1);
    }
    return 1;
}

/* Pagination Navigation */
function checkNext(id, pageCount) {
    if (id < pageCount) {
        return (id + 1);
    }
    return id;
}

/* Page Range calculation Method for Pagination */
function pageRange(page, pageCount) {

    var start = page - 2,
        end = page + 2;

    if (end > pageCount) {
        start -= (end - pageCount);
        end = pageCount;
    }
    if (start <= 0) {
        end += ((start - 1) * (-1));
        start = 1;
    }

    end = end > pageCount ? pageCount : end;

    return {
        start: start,
        end: end
    };
}