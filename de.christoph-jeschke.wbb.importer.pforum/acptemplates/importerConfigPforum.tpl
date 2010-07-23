{capture assign=additionalDBFields}
	<div class="formElement{if $errorField == 'tablePrefix'} formError{/if}">
		<div class="formFieldLabel">
			<label for="tablePrefix">{lang}wbb.acp.importer.pforum.configure.db.tablePrefix{/lang}</label>
		</div>
		<div class="formField">
			<input type="text" class="inputText" id="tablePrefix" name="settings[tablePrefix]" value="{$settings.tablePrefix}" />
			{if $errorField == 'tablePrefix'}
				<p class="innerError">
					{if $errorType == 'invalid'}{lang}wbb.acp.importer.pforum.configure.db.tablePrefix.error.invalid{/lang}{/if}
				</p>
			{/if}
		</div>
	</div>
{/capture}
{include file=importerConfigDB}

<fieldset>
	<legend>{lang}wbb.acp.importer.pforum.configure.source{/lang}</legend>

	<div class="formElement{if $errorField == 'sourcePath'} formError{/if}">
		<div class="formFieldLabel">
			<label for="sourcePath">{lang}wbb.acp.importer.pforum.configure.source.path{/lang}</label>
		</div>
		<div class="formField">
			<input type="text" class="inputText" id="sourcePath" name="settings[sourcePath]" value="{$settings.sourcePath}" />
			{if $errorField == 'sourcePath'}
				<p class="innerError">
					{if $errorType == 'invalid'}{lang}wbb.acp.importer.pforum.configure.source.path.error.invalid{/lang}{/if}
				</p>
			{/if}
		</div>
	</div>
</fieldset>