/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-07-14
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
var translate = {
	SellBuy	: {
		0	: "����",	1 : "����"
	},
	Transaction_type	: {
		1	: "ί�гɽ�",  2 : "ǿ��ƽ��", 3 : "�Զ����",  4 : "ϵͳ����"
	},
	statement_type	: {
		1	: "�ֺ�",   2 :  "����" , 3 : "����", 4 : "ϵͳ����", 5 : "�͹�", 6 : "�Զ����", 7 : "��Ϣ", 8 : "ǿ��ƽ��"
	},
	IfDealt	: {
		0	: "δ�ɽ�", 1 : "�ɽ�", 2 : "�û�����", 3 : "ϵͳ����"
	}
};
var urlInfo = "plugin.php?id=simstock:index";
function formatNum( n )
{
	return n > 9 ? ( "" +n ) : ("0" + n );
}
function msg( str )
{
	$("#alertWindow #alertContent").html( str );
	$("#alertWindow").show();
}
function isMsg()
{
	return $("#alertWindow").css("display") != "none";
}
function msgArray( arr )
{
	msg( arr.join("<br/>") );
}
function f2( condition, amt, decimal, unit )
{
	amt = parseFloat( amt );
	return ( isNaN( amt ) )  ? "--" : ( ( decimal === undefined ? amt : amt.toFixed( decimal ) ) + (unit || "") );
}
function getZeroCls( amt )
{
	amt = parseFloat( amt );
	if ( isNaN(amt) )
		return  "fgray";
	return amt > 0 ? "fred" : ( amt == 0 ? "fgray" : "fgreen" );
}
function getValidNum( a, b )
{
	return ( isNaN( a ) || parseFloat( a ) == 0 ) ? ( parseFloat( b ) || 0) : parseFloat( a );
}
$( function(){
	//�˳�
	var c = new LoginComponent({
		logout	: ".logoutbtn",
		onLogoutSuccess	: function(){window.location.href = urlInfo;},
		onLogoutFailed	: function(){if (msg) msg( "�˳�ʧ��" );}
	});
	LoginManager.init()
		.add( c )
		.startMonitor();
} );
//--------------------------------  ����ѡ����  --------------------------------------------
var ContestSelector = function( config ){
	$.extend( this, config );
	return this.init();
}
ContestSelector.prototype = {
	el		: "#contests",
	callback: function(){},
	init	: function(){
		//�л�����
		this.el = $( this.el );
		var _self = this;
		this.el.find("a").click( function( e ){
			e.preventDefault();
			if ( !$(this).parent().hasClass("selected") )
				_self.onClick( $(this).attr("cid") );
			return false;
		} );
		//��ȡ�洢��������Ϣ
		var cid = Request.QueryString( "cid" ) ||  $.cookie("contests");
		//û�ж�Ӧ������ʱ�����õ�һ������
		var elements = $("#contests a[cid='" + cid +"']");
		if ( elements.length == 0 )
			cid = $("#contests").find("a:first").attr("cid");
		this.onClick( cid );
		return this;
	},
	onClick			: function( id ){
		this.el.children("span").removeClass("selected");
		this.el.find("a[cid='" + id + "']").parent().addClass("selected");
		this.callback( this.createContest( id ) );
		$.cookie("contests", id );
	},
	createContest	: function( id ){
		return {
			id : id,
			title : "",
			percent : id == "5" ? 0.3 : 1			//��������30������
		}
	}
};
var kfsAccount = function( config ){
	$.extend( this, config );
	return this.init();
}
kfsAccount.prototype = {
	uid		: '',
	nick	: '',
	init	: function(){
		this.uid = u.uid;
		this.nick = u.uname;
		return this;
	}
};

