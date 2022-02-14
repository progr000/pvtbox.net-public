window.onload = function(){
    
    form.init();
    popUp.init();
    select.init(); 
    table.init();
    tabBlock.init();
    inform.init();
    
    $('.scrollbar-program').scrollbar();
};





/* inform -------------------------------------*/
var inform = {
    notActive: 'notActive',
    cont: '.inform-manager',
    tim: null
};

inform.init = function(){
     
    if(!$(this.cont).length) return;
    
    inform.tim = setTimeout(function(){

        clearTimeout(inform.tim); 
        $(inform.cont).fadeOut(400, function(){
            
            $(inform.cont).addClass(inform.notActive);
        });

    }, 3500); 
};





/* tabBlock -----------------------------------*/
var tabBlock = {
    active: 'active',
    button: '.tabBlock-list li',
    contentBox: '.tabBlock-content__box'
};

tabBlock.init = function(){
     
    this.events();
};

tabBlock.events = function(){
     
    $('body').on('click', this.button, function(event){
        
        $(this).parents('ul').find('li').removeClass(tabBlock.active);
        $(this).addClass(tabBlock.active);
        
        var ind = $(this).index();
        
        $(tabBlock.contentBox).removeClass(tabBlock.active);
        $(tabBlock.contentBox).eq(ind).addClass(tabBlock.active);
         
        table.act();
    });
};





/* table --------------------------------------*/
var table = {
    bl: '.table, .workspace',
    
    headCont: '.table__head-cont, .workspace__head-cont',
    bodyCont: '.table__body-cont, .workspace__body-cont',
    
    headBox: '.table__head-box, .workspace__head-box',
    bodyBox: '.table__body-box, .workspace__body-box',
    
    sizeBox: null,
    xBox: null,
    tim: null,
    x_head: null,
    x_body: null
};

table.init = function(){
     
    this.events();
};

table.events = function(){
     
    table.act();
    $(window).resize(function(){
        table.act();
    });
    
    $('#settings').on('show.bs.modal', function (e) {
       
        table.tim = setTimeout(function(){

            table.act();
            clearTimeout(table.tim);
            
        }, 500); 
    });
};

table.act = function(){
     
    for(var i=0; i<$(this.bl).length; i++){
        
        this.sizeBox = $(this.bl).eq(i).find(this.headBox).length;
        
        this.x_head = $(this.bl).eq(i).find(this.headCont).innerWidth();
        this.x_body = $(this.bl).eq(i).find(this.bodyCont).innerWidth();  
        $(this.bl).eq(i).find(this.headCont).css('paddingRight', this.x_head - this.x_body);
        
        for(var j=0; j<this.sizeBox; j++){
            
            this.xBox = $(this.bl).eq(i).find(this.bodyBox).eq(j).outerWidth();
            $(this.bl).eq(i).find(this.headBox).eq(j).outerWidth(this.xBox);  
        }
    }
};





/* select -------------------------------------*/
var select = {};
select.init = function(){

    $('.selectpicker').selectpicker({
        size: 4
    });
};





/* popUp --------------------------------------*/
var popUp = {
    active: 'active'
};

popUp.init = function(){
     
    this.events();
};

popUp.events = function(){
     
    $('#entrance').on('show.bs.modal', function (event) {

        var button = $(event.relatedTarget);
        var recipient = button.data('whatever');

        if(recipient == 'log'){
            $('.modal-body .btn-radio').eq(0).click();
        }
        if(recipient == 'reg'){
            $('.modal-body .btn-radio').eq(1).click();
        }
    });
     
    $('#getLink').on('hide.bs.modal', function (event) {
         
        $('#getLink .tab-pane').removeClass(popUp.active);
        $('#getLink .tab-pane').eq(0).addClass(popUp.active);
    });      
};





/* form ---------------------------------------*/
var form = {
    active: 'active',
    bl: '.form-block',
    button: '.btn-radio',
    tab: '.form-box'
};

form.init = function(){
     
    this.events();
};

form.events = function(){
     
    $('body').on('click', this.button, function(event){
        
        if(!$(this).hasClass(form.active)){
            
            var ind = $(this).index();
            
            $(this).parents(form.bl).find(form.tab).removeClass(form.active);
            $(this).parents(form.bl).find(form.tab).eq(ind).addClass(form.active);
        }
    });
};

















