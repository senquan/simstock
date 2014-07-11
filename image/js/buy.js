/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-07-14
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
var main = function(){
	var contest, 	//����
		digi = 2,	//С����λ��
		limitPercent = 0.1,	//ί�м۸����� STΪ0.05
		me, account, hold,	//�˻���Ϣ
		symbol, loading = false, type = "buy",
		orders,
		hq  //��ǰ��Ʊ��������
		;
	var MSG_PRICE_EMPTY = "����������۸�",
	  MSG_COUNT_EMPTY = "�������������0",
		MSG_PRICE_BUY = "�����������۸��ڹ�������Χ���������ǵ�ͣ�۸�֮��ı���",
		MSG_COUNT_100 = "��������ӦΪ100����������",
		MSG_COUNT_BUY = "��������������������Χ��",
		MSG_STOCK_EMPTY = "����ѡ���Ʊ���ٵ���µ�";
	var selector = new ContestSelector({
		callback	: function( c ){
			contest = c;
			//��ȡ����
			getAccountInfo();
			//����˵��
			$(".detail").hide();
			$(".detail[cid='" + c.id + "']" ).show();
			resetForm();
		}
	});
	//���ñ�
	function resetForm(){
		$("#stockForm").find( "input" ).val( "" );
	}
	//ѡ���Ʊ
	var sg = new SuggestServer();
	sg.bind({ target : "", "input": "searchStock", "default": "����/����/ƴ��", "type": "stock", "link": "javascript:void(0)",
				callback : function(){
					var tr = this._X || $(sg._T).find("tr")[1],
						stockname = tr.id ? tr.id.split(",")[4] : "";
					symbol = tr.id ? tr.id.split(",")[3] : "";
					if (tr && symbol) {
						$("#searchStock").val( symbol );
						$("#stockName").val( stockname );
						$("#price").val( "" );
						$("#amount").val( "" );
						$("#amountMax").val( "" );
						firstFill = false;
						firstTip = false;
						loadStock( symbol );
					}
				}
			});
	sg.changeType( "111" );
	//��ȡ��Ʊ����
	var dl = new $.HQDataLoader();
	function loadStock( symbol )
	{
		dl.stop()
		 .remove( symbol )
		 .add( symbol, {
		 	codes : [ symbol ], inter : 5000, random : true
		 } )
		 .on( symbol, showStock )
		 .start( symbol );
	}
	function timeToInt( timestr )
	{
		var time_arr=(timestr || "0:0:0") .split(":");
		return time_arr[0]*10000+time_arr[1]*100+time_arr[2]*1;
	}
	//��ʾ��Ʊ����
	var trs = $("#five").find("tr");
	var firstFill = false, firstTip = false;
	function showStock( datas ){
		var data = datas[ 0 ] , digi = 2;
		if ( !data )
			return false;
		hq = data;	//��������
		//���̺��������¹�
		var tickettime = timeToInt(data[31]);
		if ( !firstTip && /^N/i.test( data[0] ) && tickettime <= 150005 ){
			firstTip = true;
			msg( "��Ǹ���¹��ݲ��ܲ�����" );
			return false;
		}
		//��䵱ǰ��
		if (!firstFill)
		{
			firstFill = true;
			$("#price").val( getValidNum( hq[3] , hq[2] ).toFixed(2) || 0.0 );
			onChangePrice();
		}
		//ST��
		if ( /ST/i.test( data[0] ) )
		{
			limitPercent = 0.05;
		}
		//�ж�ͣ��
		if ( (!data[3] || parseFloat(data[3]) == 0) && (tickettime < 90000 || tickettime > 93000) )
		{
			$("#priceCurrent").val("ͣ��(" + f2(data[2], data[2], 2) + ")").attr( "className", "input " + (getZeroCls(0)) );
		}
		else
		{
			$("#priceCurrent").val(f2(data[3], data[3], 2)).attr( "className","input " + getZeroCls(data[3] - data[2]) );
		}
		//�嵵�̿�
		trs.eq( 5 ).children("td:eq(1)").html( f2( data[ 3 ],data[ 3 ], digi) ) //�۸�
					.addClass( getZeroCls( data[ 3 ] - data[2] ) );
		for ( var i=0; i<5; i ++ )
		{
			//������
			var tds = trs.eq( 6 + i ).children("td");
			tds.eq( 2 ).html( f2( data[3]+data[ 10 + i * 2 ] , parseInt( data[ 10 + i * 2 ] ) /100 , 0 ) ); //����
			tds.eq( 1 ).html( f2( data[3]+data[ 10 + i * 2 + 1 ], data[ 10 + i * 2 + 1 ] , digi) )
						.addClass( getZeroCls( data[ 10 + i * 2 + 1 ] - data[2] ) ) ;	//�۸�
			//������
			tds = trs.eq( 4 - i ).children("td");
			tds.eq( 2 ).html( f2( data[3]+data[ 20 + i * 2 ], parseInt( data[ 20 + i * 2 ] ) /100 , 0 ) ); //����
			tds.eq( 1 ).html( f2( data[3]+data[ 20 + i * 2 + 1 ], data[ 20 + i * 2 + 1 ] , digi ) )
						.addClass( getZeroCls( data[ 20 + i * 2 + 1 ] - data[2] ) ) ;	//�۸�
		}
	}
	//΢������۸�
	function adjust( selector, target, range, check, d )
	{
		$( selector ).click( function(){
			var v = getFloat( target );
			if ( v ){
				var n = v + range;
				n = check( n );
				$( target ).val( n );
				//�������ɹ�����
				onChangePrice();
			}
		} );
	}
	adjust( "#pricemap area:first", "#price", 0.01, checkPrice, digi );
	adjust( "#pricemap area:last", "#price", -0.01, checkPrice, digi );
	adjust( "#amountmap area:first", "#amount", 100, checkAmount, 0 );
	adjust( "#amountmap area:last", "#amount", -100, checkAmount, 0 );
	//���۸����ʱ
	function onChangePrice()
	{
		var v = getFloat("#price");
		if ( v )
		{
			//�˻����
			var remain = parseFloat( account.AvailableFund ) || 0 ;
			var spend = 0;
			var cost = v * 1.001 + ( /sh/i.test( symbol ) ? 0.001 : 0 );
			var n = parseInt( remain / cost );
			n = parseInt( n / 100 ) * 100;
			$("#amountMax").val(n);
		}
		else
		{
			$("#amountMax").val("");
		}
		onChangeAmount();
	}
	//�������۸�
	function checkPrice( p )
	{
		if ( !hq )
			return p;
		//�е�ǰ��ʱȡ��ǰ�ۣ�û��ʱȡ����
		var c = parseFloat( getValidNum( hq[3] , hq[2] ) );
		//�е�ǰ��ʱ�����ܳ�������
		if ( c )
		{
			var max = c * (1+ limitPercent ) , min = c * (1 - limitPercent);
			if ( p > max ) p = max;
			if ( p < min ) p = min;
		}
		return parseFloat(p).toFixed( digi );
	}
	//�����������
	function checkAmount( n )
	{
		var c =	getFloat("#amountMax");
		if ( n > c )
			n = c;
		return n;
	}
	//����������ʱ
	function onChangeAmount()
	{
		var v = getFloat("#price");
		var n = getFloat("#amount");
		if ( v && n != null )
		{
			$("#sum").val( (v * n).toFixed( digi ) );
		}
		else
		{
			$("#sum").val("");
		}
	}
	$("#price").blur( function(){
		var v = $(this).val();
		if (v) {
			v = parseFloat(v).toFixed( digi );
			$(this).val( v );
			var msgs= [];
			if ( v != checkPrice($(this).val()) ){
				msgs.push( MSG_PRICE_BUY );
			}
			if (msgs.length) {
				msgArray(msgs);
				//$(this).val("");
			}
			onChangePrice();
		}
	} );
	$("#amount").blur( function(){
		var v = $(this).val();
		if ( v ){
			v = parseFloat(v).toFixed( 0 );
			$(this).val( v );
			var msgs= [];
			if ( v != checkAmount($(this).val()) ){
				msgs.push( MSG_COUNT_BUY );
			}
			if ( v % 100 != 0 ){
				msgs.push( MSG_COUNT_100 );
			}
			if (msgs.length) {
				msgArray(msgs);
				//$(this).val("");
			}
			onChangeAmount();
		}
	} );
	//��ø���������
	function getFloat( selector )
	{
		var v = $( selector ).val();
		if ( v != "" && !isNaN( v ) )
		{
			return parseFloat( v );
		}
		return null;
	}
	//ǿ���������Ϊ��������
	$(".uinumber").keyup( function(){
		var v = this.value;
		var matches = /\d+(\.\d{0,2})?/.exec( v );
		if ( matches && matches[0] != undefined )
			this.value = matches[0];
		else
			this.value = "";
	} );
	$(".uinumberb").keyup( function(){
		var v = this.value;
		var matches = /\d*/.exec( v );
		if ( matches && matches[0] != undefined )
			this.value = matches[0];
		else
			this.value = "";
	} );
	//��ȡ�ʺ���Ϣ
	function getAccountInfo()
	{
		me = new kfsAccount();
		if ( !me )
			window.location.href = "plugin.php?id=simstock:index";
		loading = true;
		$("#searchStock").attr( "disabled", "true" );
		LoginManager.add({
			onLoginSuccess: function(){
				$.get(urlInfo, {mod:'ajax', section:'account', uid:me.uid, t:new Date().getTime()}, function(obj){
					if ( obj )
					{
						eval(obj);
						account	= o.account;
						hold	= o.stockHold;
						orders	= o.order;
					}
					else
					{
						msg("��ȡ�˻���Ϣʧ�ܣ���ˢ��ҳ����ٲ���");
					}
					loading = false;
					$("#searchStock").removeAttr("disabled");
				});
			}
		});
	}
	$("#getmax").click( function(){
		$("#amount").val($("#amountMax").val());
		onChangeAmount();
	});
	var submiting = false;
	$("#submit").click( function(){
		if ( submiting )
			return false;
		var p = $("#price").val(),
			n = $("#amount").val(),
			s = $("#stockName").val(),
			msgs = [];
		$("#amount").blur();
		$("#price").blur();
		if ( isMsg() )
			return false;
		if ( !symbol || !/^\w\w\d{6}$/.test( symbol ) )
			msgs.push( MSG_STOCK_EMPTY );
		if ( p == "" || p == 0 )
			msgs.push( MSG_PRICE_EMPTY );
		if ( n == "" || n == 0 )
			msgs.push( MSG_COUNT_EMPTY );
		else
		{
			if ( n != checkAmount( n ) )
			{
				msgs.push( MSG_COUNT_BUY );
			}
			if ( n % 100 != 0 ){
				msgs.push( MSG_COUNT_100 );
			}
		}
		if ( msgs.length > 0 )
		{
			msgArray(msgs);
		}
		else
		{
			submiting = true;
			$.get( urlInfo, {mod:'ajax', section:type, uid:me.uid, code:symbol, stockname:s, price:p, amount:n}, function( obj ){
				submiting = false;
				if ( obj )
				{
					eval(obj);
					if ( ret == '0' )
					{
						msg("�ύ�ɹ���");
						$("#submit").unbind("click");
						setTimeout(function(){window.location.href=urlInfo+'&mod=member&act=trustsmng';}, 1000);
					}
					else
					{
						msg(ret);
					}
				}
				else
				{
					msg("�ύʧ�ܣ���ˢ��ҳ�����²�����");
				}
			});
		}
		return false;
	});
};
main();
