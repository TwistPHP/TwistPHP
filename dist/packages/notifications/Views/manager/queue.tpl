<style>
	.showOnHover{
		display: none;
	}
	.showHover:hover .showOnHover{
		display: block;
	}
</style>

<div class="grid-100 tablet-grid-100 mobile-grid-100">
	<h2 class="no-top-padding">Twist: Notification Queue</h2>
	<a href="/notifications" class="button fat blue float-right">Refresh</a>
	<a href="?retry=all" class="button fat blue float-right confirm">Retry All Failed</a>
	<p>Outgoing notifications that are in the send queue, the queues are constantly running so items will start to send immediately, items marked as 'delete' have been sent and are purged periodically. Pressing the retry all failed button will re-add any failed sends to the list. Sometimes when sending out emails and notifications though remote servers issues can happen, in these instances there is no way of telling if the user received the initial notification but it is likely they did not.</p>
	{data:no-fly-list}
	<table>
		<thead>
		<tr>
			<td>Type</td>
			<td>UserID</td>
			<td>Message</td>
			<td>Status</td>
			<td>Added</td>
			<td>Sent</td>
		</tr>
		</thead>
		<tbody>
		{data:notifications}
		</tbody>
	</table>
</div>