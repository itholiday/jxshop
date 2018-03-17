function addBookmark(title, url) {

    if (window.sidebar) {

        window.sidebar.addPanel(title, url, "");

    } else if (document.all) {

        window.external.AddFavorite(url, title);

    } else if (window.opera && window.print) {

        return true;

    }

}

function setHome(url) {

    if (document.all) {

        document.body.style.behavior = 'url(#default#homepage)';

        document.body.setHomePage(url);

    } else if (window.sidebar) {

        if (window.netscape) {

            try {

                netscape.security.PrivilegeManager.enablePrivilege("UniversalXPConnect");

            } catch (e) {

                alert("该操作被浏览器拒绝，如果想启用该功能，请在地址栏内输入 about:config,然后将项 signed.applets.codebase_principal_support 值该为true");

            }

        }

        if (window.confirm("你确定要设置" + url + "为首页吗？") == 1) {

            var prefs = Components.classes['@mozilla.org/preferences-service;1'].getService(Components.interfaces.nsIPrefBranch);

            prefs.setCharPref('browser.startup.homepage', url);

        }

    }

}


//<a href="javascript:void(null)" onClick="setHome('http://www.jb51.net');">设为首页</a>
//<a href="javascript:addBookmark('脚本之家','http://www.jb51.net')">加入收藏</a>


