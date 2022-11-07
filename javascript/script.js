$(document).ready(function(){ 
  // Tab
    $('.tab-a').click(function(){  
      $(".tab").removeClass('tab-active');
      $(".tab[data-id='"+$(this).attr('data-id')+"']").addClass("tab-active");
      $(".tab-a").removeClass('active-a');
      $(this).parent().find(".tab-a").addClass('active-a');
     });
    //  data table
    if(document.getElementById('my_request')){
        $('#my_request').DataTable({
          order: [[5, 'desc']],
        });
    }
    if(document.getElementById('staff_request')){
      $('#staff_request').DataTable({
        order: [[7, 'desc']],
      });
    }
    if(document.getElementById('all_request')){
        $('#all_request').DataTable({
          order: [[7, 'desc']],
        });
    }
    if(document.getElementById('tms_course_detail')){
      $('#tms_course_detail').DataTable({
        "lengthMenu": [[25, 50, 100, -1], [25, 50, 100, "All"]],
         "columnDefs": [{ targets: 'no-sort', orderable: false }],
        "oSearch": { "bSmart": false, "bRegex": true },
        "language": {
          search: 'Search:',
        },
        //  dom: "Blfrtip",
        //  buttons:  [{
        //         extend: 'excel',
        //         text: 'Export to Excel',
        //         filename:'Participant List',
        //         header:'header',
        //         // messageTop:'message top',
        //         title:'',
        //         exportOptions: {
        //             modifier: {
        //                 page: 'current'
        //             },
        //             trim: false,
        //             stripHtml: true,
        //                format: {
        //             header: function ( html, index, node ) {
        //               switch(index) {
        //                   case 2:
        //                      return "Country";
        //                     break;
        //                   case 3:
        //                     return "Account Type";
        //                     break;
        //                   case 4:
        //                   return "User Type";
        //                   break;
        //                   case 5:
        //                     return "Batch";
        //                     break;
        //                   default:
        //                   return html;
        //                     // code block
        //                 } 
        //               //         if (index > 1 && index < 5 ) {
        //               //   var firstWord = html.split(" ")[0];
        //               //   return firstWord;
        //               // }else{
        //               //  return html;
        //               // };
        //               // return index + ' [' + html +']';
        //             }
        //           }
        //         }
        //     }],
      //   footerCallback: function (row, data, start, end, display) {
         
      //     var api = this.api();

      //     // Remove the formatting to get integer data for summation
      //     var intVal = function (i) {
      //         return typeof i === 'string' ? Number(i) : typeof i === 'number' ? i : 0;
      //     };
      //     let total2 = 0;
      //     api.column(3,{ page: 'all'}).every( function (){
      //       // if(this.visible() == true){
      //       //   alert(this.data());
      //       // }
      //     //   if(this.visible() == true){
      //     //   var sum = this
      //     //       .data()
      //     //       .reduce( function (a,b) {
      //     //           return a + b;
      //     //       } );
      //     //     }
      //     //   alert(sum);
      //     // });
      //     // // Total over all pages
      //     // total = api
      //     //     .column(3,{ page: 'all'})
      //     //     .data()
      //     //     .reduce(function (a, b) {
      //     //         return intVal(a) + intVal(b);
      //     //     }, 0);
        
      //     // Total over this page
      //     // pageTotal = api
      //     //     .column(3, { page: 'current' })
      //     //     .data()
      //     //     .reduce(function (a, b) {
      //     //         return intVal(a) + intVal(b);
      //     //     }, 0);

      //     // Update footer

      //     // $(api.column(10).footer()).html(' total11');
      //     $('#text1').html(total+ "html");
      // },
        //  initComplete: function () {
        //     this.api().columns([4]).every( function () {
        //     var column = this;
        //     var years =[];
        //     $('#tms_course_detail .head .head_hide').html('');
    
        //     var select = $('<select class="filterdropdown"><option value="">'+$(column.header()).text()+'</option></select>')
        //         .appendTo( $(column.header()).empty())
        //         .on( 'change', function () {
        //             // var val = $.fn.dataTable.util.escapeRegex(
        //             //     $(this).val()
        //             // );
        //             // alert($(this).val());
        //             var val = $(this).val();
        //             column
        //                 .search( val ? '\\b'+val+'\\b' : '', true, false )
        //                 .draw();
        //         });
    
        //     column.data().unique().sort().each( function ( d, j ) {
        //         let dateObj = new Date(d);
        //         let yearOnly = dateObj.getFullYear();
        //         if(!years.includes(yearOnly)){
        //           select.append( '<option value="'+yearOnly+'">'+yearOnly+'</option>' );
        //           years.push(yearOnly);
        //         }
        //     });
        // }); 
        // },
      });
  }
});
