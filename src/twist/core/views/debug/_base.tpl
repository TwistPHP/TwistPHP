<style>

	html{
		margin-bottom:20px;
	}
	.twistDebug{
		z-index: 1001;
		font-size: 11px;
		color: #3d3d3d;
		position:fixed;
		bottom:0;
		left:0;
		right:0;

		min-height:20px;
		border-top:1px solid #333333;

		background-color: #CCC;
	}

	.twistDebug h3{
		font-size: 1.2em;
		margin: 5px;
	}

	.twistDebugOpen{
		resize: vertical;
		height:280px;
	}

	.twistDebugOpen .twistTab{
		position: absolute;
		top:34px;
		left:0;
		right:0;
		bottom:0;

		overflow: auto;
		background-color: #FFF;
	}



	.twistDebugOpen pre {
		margin-bottom: 1em;
		display: block;
		font-family: "Courier New", Courier, monospace, mono;
		font-size: 1.3em;
		line-height: 1.25em;
		white-space: pre-wrap; }

	.twistDebugOpen pre.code {
		padding: 1em;
		position: relative;
		overflow: auto;
		white-space: pre;
		background-color: #EEE;
		border: 1px #CCC solid;
		border-radius: 3px; }

	.twistDebugOpen pre.code em {
		font-style: normal;
		font-weight: bold;
		background-color: #FF0; }
	.twistDebugOpen pre.code[title] {
		padding-top: 2.5em; }
	.twistDebugOpen pre.code[title]::before {
		padding: 0 0.75em;
		position: absolute;
		top: 0;
		right: 0;
		left: 0;
		color: #FFF;
		font-weight: bold;
		font-family: Helvetica, Arial, Tahoma, sans-serif;
		line-height: 1.5em;
		white-space: nowrap;
		background-color: #CCC;
		content: attr(title); }
	.twistDebugOpen pre.code[lang]::after {
		height: 1.5em;
		padding: 0 0.5em;
		position: absolute;
		top: 0;
		right: 0.5em;
		color: #FFF;
		font-family: Helvetica, Arial, Tahoma, sans-serif;
		line-height: 1.5em;
		text-transform: uppercase;
		background-color: #CCC;
		border-radius: 0 0 3px 3px;
		content: attr(lang); }
	.twistDebugOpen pre.code[lang][title]::after {
		padding: 0;
		right: 0.75em;
		background-color: transparent; }




	.twistDebugOpen div.hide{
		display: none;
	}

	.twistDebug ul{
		border-bottom:1px solid #A9A9A9;
		margin:0;
		padding:0;
	}

	.twistDebug ul li{
		display: inline-block;
		border-right:1px solid #333333;
		list-style: none;
		padding:6px 4px;
		cursor: pointer;
	}

	.twistDebug ul li:hover{
		background-color: #EAEAEA;
	}

	.timer{
		margin:auto;
		width:90%;
		border: 1px solid #222;
		height:35px;
	}

	.timer span{
		display: inline-block;
		vertical-align: top;
		text-align: center;
		color:#FFF;
		font-weight:bold;
		padding-top:10px;
		height:25px;
	}

</style>
<div class="twistDebug twistDebugOpen">
	<ul>
		<li><img src="/twist/core/resources/logos/logo-32.png" title="TwistPHP" alt="TwistPHP">Debug</li><!--
		--><li onclick="twistDebugTab('twistTabError');">Errors</li><!--
		--><li onclick="twistDebugTab('twistTabDatabase');">Database</li><!--
		--><li onclick="twistDebugTab('twistTabTemplates');">Templates</li><!--
		--><li onclick="twistDebugTab('twistTabStats');">Stats</li><!--
		--><li onclick="twistDebugTab('twistTabCache');">Cache</li>
	</ul>
	<div class="twistTab twistTabError">
		<h3>Errors</h3>
		<p>List all the PHP errors that have occurred during the processing of this page</p>
		{data:errors}
	</div>
	<div class="twistTab twistTabDatabase hide">
		<h3>Database</h3>
		<p>List all the database queries run and status generated from processing this page</p>
		{data:database}
	</div>
	<div class="twistTab twistTabTemplates hide">
		<h3>Templates</h3>
		<p>List all the templates, elements and routes used in the generation of this page</p>
		<table>
			<thead>
				<tr>
					<th>Instance</th>
					<th>File</th>
					<th>Tags</th>
				</tr>
			</thead>
			<tbody>
				{data:templates}
			</tbody>
		</table>
	</div>
	<div class="twistTab twistTabStats hide">
		<h3>Stats</h3>
		<p>Page load time stats, memory usage and other useful information</p>
		{data:stats}
	</div>
	<div class="twistTab twistTabCache hide">
		<h3>Cache</h3>
		<p>Page load time stats, memory usage and other useful information</p>
		<table>
			<thead>
			<tr>
				<th>Instance</th>
				<th>Key</th>
				<th>Size</th>
				<th>Expiry</th>
				<th>Tools</th>
			</tr>
			</thead>
			<tbody>
			{data:cache}
			</tbody>
		</table>
	</div>
</div>
<script type="text/javascript">
	function twistDebugTab(strTabClass){
		$('.twistTab').removeClass('hide').addClass('hide');
		$('.'+strTabClass).toggleClass('hide');
	}
	$(".twistDebug").resizable({ handles: "s" });
</script>