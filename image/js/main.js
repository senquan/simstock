/*
 * Kilofox Services
 * SimStock v1.0
 * Plug-in for Discuz!
 * Last Updated: 2011-07-17
 * Author: Glacier
 * Copyright (C) 2005 - 2011 Kilofox Services Studio
 * www.Kilofox.Net
 */
function mainFunction()
{
	var contest, //����
		digi = 2, //С����λ��
		limitPercent = 0.1, //ί�м۸����� STΪ0.05
		me, account, hold, //�˻���Ϣ
		symbol, loading = false, type = "buy", orders, from = 0, count = 6, orderby = "season_profit_ratio", ordertype = "desc", friends, follows, uid, home = false, nickname, hq //��ǰ��Ʊ��������
		;
	var dl = new $.HQDataLoader();
	var selector = new ContestSelector({
		callback: function(c){
			contest = c;
			//��ȡ����
			getAccountInfo();
		}
	});
	//��ȡ�ʺ���Ϣ
	function getAccountInfo()
	{
		me = new kfsAccount();
		if ( !me )
			window.location.href = urlInfo;
		uid = Request.QueryString("uid") || me.uid; //ҳ��Ӧ����ʾ˭����Ϣ
		loading = true;
		LoginManager.add({
			onLoginSuccess: function(){
				$.get(urlInfo, {mod:'ajax', section:'account', uid:me.uid, t:new Date().getTime()}, function(obj){
					if ( obj )
					{
						eval(obj);
						account	= o.account;
						hold	= o.stockHold;
					}
					else
					{
						msg("��ȡ�˻���Ϣʧ�ܣ���ˢ��ҳ����ٲ�����");
					}
					loading = false;
					//handleTopNav();
					showAccountInfo();
					showHoldStock();
				});
			}
		});
	}
	function handleTopNav()
	{
		//��ʾ�û���
		var names = $(".username");
		names.text((account && account.NickName) || me.nick || "�û�").attr("href", "main.html?uid=" + uid);
		if (!me)
		{
			//δ��¼
			$("#visitor").show();
		}
		else
		{
			home = me.uid == uid;
			$(".onlyname").text( (account && account.NickName) || me.nick || "�û�" );
			$(".onlyuid").each( function(){
				$(this).attr( "href", $(this).attr( "href" ) + uid );
			} );
			if (home) {
				//�鿴�Լ���ҳ
				$("#myself").show();
			}
			else {
				//�鿴������ҳ
				$("#neighbour").show();
				//�����ע����
				$("#sub_tit span a").each(function(){
					$(this).text($(this).text().replace("��", "TA")).attr("href", $(this).attr("href") + "&uid=" + uid);
				});
				//�����ʻ��ֲֵ���ת
				$("#sub_tit01").hide();
				var a = $("#sub_tit02").show().find("span:last a");
				a.attr("href", a.attr("href").replace("${uid}", uid));
				//�����ע
				$.getJSONP(urlInfo, "Follow_Service.isFollowingOne", {
					uided: uid,
					uiding: me.uid
				}, function(obj){
					if (obj) {
						if (obj.retcode == "1") {
							$("#delFriend").show();
							$("#addFriend").hide();
						}
						else {
							$("#delFriend").hide();
							$("#addFriend").show();
						}
					}
				});
				//��ӹ�ע
				$("#addFriend").click(function(){
					$.getJSONP(urlInfo, "Follow_Service.writeFollowInfo", {
						uid: uid
					}, function(obj){
						if (obj && obj.retcode == "0") {
							$("#delFriend").show();
							$("#addFriend").hide();
						}
					});
					return false;
				});
				//ɾ����ע
				$("#delFriend").click(function(){
					$.getJSONP(urlInfo, "Follow_Service.delFollowingData", {
						uid: uid
					}, function(obj){
						if (obj && obj.retcode == "0") {
							$("#delFriend").hide();
							$("#addFriend").show();
						}
					});
					return false;
				});
			}
		}
	}
	function showAccountInfo()
	{
		$("#InitFund").text(f2(account.InitFund, account.InitFund, 2, " Ԫ"));
		$("#AvailableFund").text(f2(1, account.AvailableFund, 2, " Ԫ"));
		$("#d5_profit_ratio").text(f2(1, account.d5ProfitRatio, 2, "%"));
		$("#profit_sell_ratio").text(f2(1, account.ProfitSellRatio, 2, "%"));
		$("#TotalRank").text(f2(1, account.TotalRank, 0));
		//��ʾ�Ǽ�
		var starlevel = account.StarLevel || 0;
		var level = $("#level").empty();
		var total = 8;
		for (var i = 0; i < starlevel; i++)
		{
			$('<em class="star"></em>').appendTo(level);
		}
		for (var i = starlevel; i < total; i++)
		{
			$('<em class="emptystar"></em>').appendTo(level);
		}
	}
	//�����Ʊ��ֵ
	function calcFund(datas)
	{
		// ��Ʊ��ֵ
		var stockFund = 0;
		$(hold).each(function(i, n){
			var data = datas[i]; //hq ���鴮
			stockFund += n.StockAmount * 1 * getValidNum(data[3], data[2]);
		});
		$("#StockFund").text(f2(1, stockFund, 2, " Ԫ"));
		// �ʻ���ֵ
		var total = stockFund + account.AvailableFund * 1 + account.WarrantFund * 1;
		$("#TotalFund").text(f2(1, total, 2, " Ԫ"));
		// �˻�ӯ��
		var profit = total - account.InitFund * 1;
		$("#StockProfit").text(f2(1, profit, 2, " Ԫ"));
		// ����������
		if ( !account.LastTotalFund || parseFloat(account.LastTotalFund) == 0 )
			account.LastTotalFund = account.InitFund;
		account.d1_profit_ratio = 100 * (total - account.LastTotalFund) / (account.LastTotalFund * 1);
		$("#d1_profit_ratio").text(f2(1, account.d1_profit_ratio, 2, "%"));
		// ����������
		account.total_profit_ratio = 100 * (total - account.InitFund * 1) / (account.InitFund * 1);
		$("#total_profit_ratio").text(f2(1, account.total_profit_ratio, 2, "%"));
	}
	function processHoldStock()
	{
		var codes = [];
		$(hold).each(function(i, n){
			codes.push(n.StockCode);
		});
		// û�гֹ�ʱ����ͣHQ
		if ( codes.length == 0 )
		{
			dl.stop().remove(contest.id);
			mergeHoldStock([]);
			return;
		}
		dl.stop().remove(contest.id).add(contest.id, {
			codes: codes,
			inter: 5000,
			random: true,
			loop: true
		}).on(contest.id, mergeHoldStock).start(contest.id);
	}
	var listBody = $("#holdList tbody");
	function mergeHoldStock(datas)
	{
		var trs = listBody.children("tr");
		$(datas).each(function(i, n){
			var tr = trs.eq(i);
			tr.children(".stockcode").text(hold[i].StockCode);
			tr.children(".stockname").text(hold[i].StockName);
			//û�п��̼�ʱ��������
			var value = getValidNum(n[3], n[2]);
			tr.children(".currentvalue").text(f2(value, value * hold[i].StockAmount , 0)); //�ֹ���ֵ
			tr.children(".currentprice").text(f2(value, value, 2));	// ��ǰ��
			tr.children(".costfund").text(hold[i].CostFund);
			tr.children(".stockamount").text(hold[i].StockAmount);
			tr.children(".availsell").text(hold[i].AvailSell);
			// ����ӯ��=��ǰ��ֵ-�ֹ��ܳɱ�
			var fdyk = ( value - hold[i].CostFund ) * hold[i].StockAmount;	
			// ӯ������=����ӯ��/�ֹ��ܳɱ�
			var ykbl = 100 * fdyk / ( hold[i].CostFund * hold[i].StockAmount );	
			tr.children(".fdyk").text(f2(1, fdyk, 2));	// ����ӯ��
			tr.children(".ykbl").text(f2(1, ykbl, 2, "%"));	// ӯ������
			tr.attr("className", getZeroCls(fdyk));
		});
		//�����Ʊ��ֵ
		calcFund(datas);
	}
	function showHoldStock()
	{
		//$("#holdList tbody").empty();
		if($("#holdList tbody").length!=0){
			if (hold && hold.length > 0)
				$("#holdTemplate").tmpl(hold).appendTo($("#holdList tbody"));
		}
		processHoldStock();
	}
	//��ע����
	$('#sub_tit span').mouseover(function(){
		$(this).addClass("selected").siblings().removeClass();
		$(".sub_cont > table").hide().eq($('#sub_tit span').index(this)).show();
		$("#getmorefriends").attr("href", $(this).children("a").attr("href"));
	});
	//�����û�
	var s = $("#searchperson");
	var oriText = s.val();
	s.focus( function(){
		var v = $.trim( s.val() );
		if ( v == oriText )
			s.val( "" );
	} ).blur( function(){
		var v = $.trim( s.val() );
		if ( v == "" )
			s.val( oriText );
	} );
	$("#searchForm").submit( function( e ){
		var v = $.trim( s.val() );
		if ( v == "" || v == oriText ){
			e.preventDefault();
			return false;
		}
		$("#searchvalue").val( encodeURIComponent( v ) );
	} );
};
mainFunction();
