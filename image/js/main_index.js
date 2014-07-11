/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-08-10
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
(function( $ ){
window.Industry = function( config ){
	$.extend( this, config );
	this.init();
	return this;
};
Industry.prototype = {
	count	: 5,
	init	: function()
	{
		this.up = [];   //����
		this.down = [];  //���
		this.upTable = $(this.upTable);
		this.downTable = $(this.downTable);
	},
	fire	: function()
	{
		this.sort();
		this.showData();
	},
	sort	: function()
	{
		if (window.hq_str_S_Finance_bankuai_sinaindustry_up)
		{
			var s = window.hq_str_S_Finance_bankuai_sinaindustry_up;
			eval( "this.up = " + s );
			var s = window.hq_str_S_Finance_bankuai_sinaindustry_down;
			eval( "this.down = " + s );
		}
	},
	//˵��������ҵ���͡�:����ҵ���ͣ��������ƣ�������Ʊ�������ۣ��ǵ���ǵ������ɽ������֣����ɽ����Ԫ�������ǹɣ����ǹ��ǵ��������ǹɵ�ǰ�ۣ����ǹ��ǵ�����ǹɼ�ƣ�����ɣ�������ǵ���������ɵ�ǰ�ۣ�������ǵ������ɹɼ�ơ�
	showData	: function()
	{
		var body = this.createBody( this.up, 8 );
		this.upTable.find("tbody").replaceWith( body );
		var body = this.createBody( this.down, 8 );
		this.downTable.find("tbody").replaceWith( body );
	},
	createBody : function( arr, index )
	{
		var body = $("<tbody>");
		for (var i=0; i<this.count; i++)
		{
			var item = arr[ i ].split(","),
				tr = $("<tr>");
			$("<td>").html( i+1 ).appendTo( tr );
			$("<td>").html( item[1] ).appendTo( tr );
			$("<td>").html( parseFloat(item[5]).toFixed(2) + "%" ).addClass( this.getCls( item[5] ) ).appendTo( tr );
			$("<td>").html( (item[index] || "") == "" ? "--" : ('<a href="plugin.php?id=simstock:index&mod=stock&act=showinfo&code=' + item[index] + '">' + (item[index+4] || "--") + '</a>') ).appendTo( tr );
			$("<td>").html( isNaN(item[index+1]) ? "--" : (parseFloat(item[index+1]).toFixed(2) + "%") ).addClass( isNaN(item[index+1]) ? "" : this.getCls( item[index+1] ) ).appendTo( tr );
			body.append( tr );
		}
		return body[0];
	},
	getCls	: function( amt )
	{
		amt = parseFloat( amt );
		return amt > 0 ? "fred" : ( amt == 0 ? "" : "fgreen" );
	},
	timer	: null,
	inter	: 1000 * 60,
	start	: function()
	{
		var _self = this;
		var fn =  function()
		{
			$.getScript( "http://hq.sinajs.cn/?_=" + (+new Date()) + "&list=S_Finance_bankuai_sinaindustry_down,S_Finance_bankuai_sinaindustry_up", function(){
				_self.fire();
			} );
		};
		this.timer = setInterval(fn, this.inter );
		fn();
	}
};
})( jQuery );
Industry = new Industry( {  upTable : "#upTable",  downTable : "#downTable"  } );
Industry.start();
jQuery( function(){
	var $ = jQuery;
	//���й�Ʊ����
	var listMgr = new $.HQListMgr();
	listMgr.push( new $.HQList({ el : "#allUpTable", defaults : {
					link	: "plugin.php?id=simstock:index&mod=stock&act=showinfo&code=@code@",
					rvar : "0",
					current : 2,  rate : 3, titleIndex : 1,
					create	: function()
					{
						if ( this.index >= 10 )
							return;
						var tr = $("<tr>");
						var r = this.getRate(), cls = "";
						if ( r > 0 )
						{
							cls = this.upCls;
							if ( this.pre )
								r = "+" + r;
						}
						else if ( r < 0 )
						{
							cls = this.downCls;
						}
						$("<td>").html( (this.index+1) ).appendTo( tr );
						$("<td>").html( this.link === false ? this.getTitle() : ("<a href='" + this.getLink() + "'>" + this.getTitle() + "</a>") ).appendTo( tr );
						$("<td>").html( r + this.unit ).addClass( cls ).appendTo( tr );
						return tr;
					}
				}, combine : true, combineVar : "new_all_changepercent_up" , combineLen : 10
			 }) );
	listMgr.push( new $.HQList({ el : "#allDownTable", defaults : {
					link	: "plugin.php?id=simstock:index&mod=stock&act=showinfo&code=@code@",
					rvar : "0",
					current : 2,  rate : 3, titleIndex : 1,
					create	: function(){
						if ( this.index >= 10 )
							return;
						var tr = $("<tr>");
						var r = this.getRate(), cls = "";
						if ( r > 0 )
						{
							cls = this.upCls;
							if ( this.pre )
								r = "+" + r;
						}
						else if ( r < 0 )
						{
							cls = this.downCls;
						}
						$("<td>").html( (this.index+1) ).appendTo( tr );
						$("<td>").html( this.link === false ? this.getTitle() : ("<a href='" + this.getLink() + "'>" + this.getTitle() + "</a>") ).appendTo( tr );
						$("<td>").html( r + this.unit ).addClass( cls ).appendTo( tr );
						return tr;
					}
				}, combine : true, combineVar : "new_all_changepercent_down" , combineLen : 10
			 }) );
	listMgr.start();
} );
