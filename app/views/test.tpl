<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta charset="utf-8" />
    {if !empty($title)}
        <title>{$title}</title>
    {/if}
    {foreach from=$scripts item='script'}
        <script src="{$script}"></script>
    {/foreach}
    {foreach from=$styles item='style'}
        <link rel="stylesheet" href="{$style}" />
    {/foreach}
</head>

<body>
    <div class="container">
        test <b>{$test}</b> smarty
        {$test|var_dump}
    </div>
</body>