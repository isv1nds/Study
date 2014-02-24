;
(function (window)
{
	if (window.BX.frameCache) return;

	var BX = window.BX;

	BX.frameCache = function ()
	{
		this.tableParams = {};
	};

	BX.frameCache.init = function ()
	{

		this.tableParams =
		{
			tableName: "bxcache",
			fields: [
				{name: "id", unique: true},
				"content",
				"hash"
			]
		};

		this.cacheDataBase = new BX.dataBase({
			name: "Database",
			displayName: "BXCacheBase",
			capacity: 1024 * 1024 * 20,
			version: "1.0"
		});

		this.cacheDataBase.createTable(this.tableParams);

		this.vars = {
			page_url: "",
			params: {},
			dynamic: []
		};

		this.lastReplacedBlocks = false;
	};

	BX.frameCache.processData = function (block)
	{
		BX.ajax.processRequestData(
			block,
			{
				scriptsRunFirst: true,
				dataType: "HTML",
				emulateOnload: true
			}
		);
	};


	BX.frameCache.update = function ()
	{
		if (BX.frameCache.vars.dynamic.length>0 && (!this.lastReplacedBlocks || this.lastReplacedBlocks.length > 0))
		{
			this.lastReplacedBlocks = [];
			this.invokeCache();
		}
		else
		{
			setTimeout(BX.proxy(this.requestData, this), 0);
		}
	};

	BX.frameCache.invokeCache = function ()
	{
		//getting caching dynamic blocks
		if (this.vars.dynamic && this.vars.dynamic.length > 0)
		{
			BX.onCustomEvent(this, "onCacheInvokeBefore", [this.vars.dynamic]);
			this.readCacheWithID(this.vars.dynamic, BX.proxy(this.insertFromCache, this));
		}

	};

	BX.frameCache.requestData = function ()
	{
		//the request headers preparing
		var headers = [
			{
				name: "BX-ACTION-TYPE",
				value: "get_dynamic"
			}
		];

		if (this.vars.PAGE_URL && this.vars.PAGE_URL.length > 0)
		{
			headers[headers.length] = {
				name: "BX-APPCACHE-PARAMS",
				value: JSON.stringify(this.vars.PARAMS)
			};

			headers[headers.length] = {
				name: "BX-APPCACHE-URL",
				value: this.vars.PAGE_URL
			};

		}

		BX.onCustomEvent(this, "onCacheDataRequestStart", []);

		BX.ajax({
			timeout: 60,
			method: 'POST',
			url: this.vars.PAGE_URL + (this.vars.PAGE_URL.indexOf('?') >= 0 ? '&' : '?') + 'r=' + Math.round((Math.random()*1000)),
			data: {},
			headers: headers,
			processData: false,
			onsuccess: BX.proxy(function (response)
			{
				json = null;

				try
				{
					json = JSON.parse(response);
				}
				catch(e)
				{
					BX.onCustomEvent(this, "onFrameDataReceivedError", [response]);
				}

				//console.log(json);
				if(json == null)
					return;

				var vars = json.lang;

				if (vars)
				{
					for (var key in vars)
					{
						BX.message[key] = vars[key];
					}
				}

				BX.onCustomEvent(this, "onFrameDataReceived", [json]);



				if (json.dynamicBlocks && json.dynamicBlocks.length > 0)//we have dynamic blocks
				{
					//this.insertBlocks({"items": json.dynamicBlocks}, true);
					this.writeCache(json.dynamicBlocks);
				}


				if (json.isManifestUpdated == "1")//the manifest has been changed
				{
					window.applicationCache.update();
				}

				BX.closeWait();
			}, this),
			onfailure: function ()
			{
				//some error
				BX.onCustomEvent(this, "onFrameDataRequestFail", json);
			}
		});
	};

	BX.frameCache.insertFromCache = function(blocks,animate)
	{

		this.insertBlocks(blocks, animate);
		this.requestData();
	};

	BX.frameCache.insertBlocks = function (blocks, animate)
	{
		var el = false;
		var block = {};
		var skip = false;
		var useHash = true;
		if (this.lastReplacedBlocks.length == 0)
			useHash = false;
		animate = false;

		for (var i = 0; i < blocks.items.length; i++)
		{

			block = blocks.items[i];
			skip = false;
			el = BX("bxdynamic_" + block.ID);
			BX.onCustomEvent(this, "onDynamicBlockCachedBefore", block);
			if (useHash)
			{
				for (var j = 0; j < this.lastReplacedBlocks.length; j++)
				{
					if (this.lastReplacedBlocks[j].ID == block.ID && this.lastReplacedBlocks[j].HASH == block.HASH)
					{
						skip = true;
						break;
					}
				}
			}

			if (el && !skip)
			{
				if (animate)
				{
					BX.fx.hide(el, "fade", {
						hide: false,
						time: 0.2,
						callback_complete: function ()
						{
							el.innerHTML = block.CONTENT
							BX.fx.show(el, "fade", {
								time: 0.2
							});
						}
					});
				}
				else
					el.innerHTML = block.CONTENT;//insert the block
				this.processData(block.CONTENT);//eval the block
			}
		}
		BX.onCustomEvent(this, "onFrameDataProcessed", [blocks]);
//		console.log("InsertFromCache");
		this.lastReplacedBlocks = blocks.items;
	};

	BX.frameCache.writeCache = function (blocks)
	{
		for (var i = 0; i < blocks.length; i++)
		{
			this.writeCacheWithID(blocks[i].ID, blocks[i].CONTENT, blocks[i].HASH)
		}

	};

	BX.frameCache.writeCacheWithID = function (id, content, hash)
	{
		this.cacheDataBase.getRows(
			{
				tableName: this.tableParams.tableName,
				filter: {id: id},
				success:
				BX.proxy(
				function (res)
				{
					if (res.items.length > 0)
					{
						if (hash == res.items[0].HASH)
							return;

						this.cacheDataBase.updateRows(
							{
								tableName: this.tableParams.tableName,
								updateFields: {
									content: content,
									hash: hash
								},
								filter: {
									id: id
								}
							}
						);
					}
					else
					{
						this.cacheDataBase.addRow(
							{
								tableName: this.tableParams.tableName,
								insertFields: {
									id: id,
									content: content,
									hash: hash
								}
							}
						);
					}

				},this),
				fail: BX.proxy(function (e)
				{
					this.cacheDataBase.addRow
					(
						{
							tableName: this.tableParams.tableName,
							insertFields: {
								id: id,
								content: content,
								hash: hash
							},
							success: function (res)
							{
							}
						}
					);
				}, this)
			});
	};

	BX.frameCache.readCacheWithID = function (id, callback)
	{

		this.cacheDataBase.getRows
		(
			{
				tableName: this.tableParams.tableName,
				filter: {id: id},
				success: BX.proxy(callback, this)
			}
		);
	};

//initialize

BX.frameCache.init();

})(window);
