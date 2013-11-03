
	{if !$files_limit_reached}
		<form action="{$form_action}" method="post" enctype="multipart/form-data">
		
			<input class="upload" type="file" name="file">
			<input class="button" type="submit" name="submit" value="Загрузить">
			
			<span class="size_restriction">Ограничение на размер: {$upload_max_size}</span>
		</form>
	{/if}

	{if $errors}
		<div class="errors">
			{foreach item=error from=$errors}
				<div class="error">{$error}</div>
			{/foreach}			
		</div>
	{/if}

	
	<ul class="file_list">
		{foreach item=file from=$files}
			<li>
				<a class="delete" href="{$file->delete_link}">удалить</a>
				<span class="size">{$file->size_str}, {$file->width}x{$file->height}</span>
				<a class="thumb" href="{$file->url}" target="_blank">
					<img alt="" src="{$file->thumbnail_url}">
				</a>				
				<a class="name" href="{$file->url}" target="_blank">{$file->original_filename}</a>
				
				
			</li>
		{/foreach}
	</ul>
	
