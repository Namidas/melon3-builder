/*melon3 environment start*/
/*melon3 environment start*/

<%if $options['scss_map'] %>
$env: <%$env|encode:'scss'%>;
<%/if%>

<%if $options['css_vars'] %>
:root {
	<%$env|encode:'css':['options' => ['key_pre' => '--env-']]%>
}
<%/if%>

/*melon3 environment end*/
/*melon3 environment end*/

