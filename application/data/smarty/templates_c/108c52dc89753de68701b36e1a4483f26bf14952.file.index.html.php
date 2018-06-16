<?php /* Smarty version Smarty 3.1.0, created on 2018-06-16 17:08:24
         compiled from "D:\workspace\search.dejson.com/static/tpl\index.html" */ ?>
<?php /*%%SmartyHeaderCode:20325b24c564ae8f57-86702220%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '108c52dc89753de68701b36e1a4483f26bf14952' => 
    array (
      0 => 'D:\\workspace\\search.dejson.com/static/tpl\\index.html',
      1 => 1529140072,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '20325b24c564ae8f57-86702220',
  'function' => 
  array (
  ),
  'version' => 'Smarty 3.1.0',
  'unifunc' => 'content_5b24c564c5104',
  'variables' => 
  array (
    'data' => 0,
  ),
  'has_nocache_code' => false,
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5b24c564c5104')) {function content_5b24c564c5104($_smarty_tpl) {?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=UTF-8">
<title><?php echo $_smarty_tpl->tpl_vars['data']->value['title'];?>
</title>
<meta name="keywords" content="<?php echo $_smarty_tpl->tpl_vars['data']->value['keywords'];?>
"/>
<meta name="description" content="<?php echo $_smarty_tpl->tpl_vars['data']->value['description'];?>
"/>
</head>
<body>
    <?php echo $_smarty_tpl->tpl_vars['data']->value['context'];?>

</body>
</html>
<?php }} ?>