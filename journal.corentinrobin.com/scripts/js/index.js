// Auteur : Corentin Robin - Version : 03/10/2018

word = function(key)
{
    if(Journal.edition != "uk") return Journal.wording[key];
    else return key;
}

Number.prototype.plural = function()
{
    return (this > 1 ? "s" : "");
}

var Journal =
{
    links : undefined,

    showLoadingMessage : function()
    {
        document.querySelector("div.loading-message").classList.add("visible");
    },

    hideLoadingMessage : function()
    {
        document.querySelector("div.loading-message").classList.remove("visible");
    },

    changeEdition : function()
    {
        var element = document.querySelector("select");

        window.location = element.value;
    },

    refresh : function()
    {
        Journal.showLoadingMessage();

        var request = new XMLHttpRequest();
        request.open("POST", "/scripts/php/api.php");
        request.setRequestHeader("Content-Type", "application/json");

        request.onreadystatechange = function()
        {
            if(this.readyState == XMLHttpRequest.DONE)
            {
                Journal.hideLoadingMessage();
                Journal.links = JSON.parse(this.response);
                Journal.displayLinks(Journal.links);
            }
        }

        request.send(JSON.stringify({ "edition" : Journal.edition }));
    },

    displayLinks : function(links, keyword)
    {
        var HTML = '', i, url, currentLinks;
        var urlsCount = 0, linksCount = 0;

        for(url in links)
        {
            urlsCount++;

            currentLinks = links[url];
            HTML += '<div><h2>' + url + ', ' + currentLinks.length + ' ' + word("article") + currentLinks.length.plural() + '</h2><div>';

            for(i = 0; i < currentLinks.length; i++)
            {
                linksCount++;

                linkTitle = currentLinks[i][1];

                if(keyword != undefined)
                    linkTitle = linkTitle.replace(new RegExp("(" + keyword + ")", "gi"), '<span class="highlighted">$1</span>');

                HTML += '<a href="' + currentLinks[i][0] + '" target="_blank">' + linkTitle + '</a>'
            }

            HTML += '</div></div>';
        }

        document.querySelector(".links").innerHTML = HTML;
        document.querySelector(".statistics").innerHTML = "<b>" + urlsCount + "</b> " + word("website") + urlsCount.plural() + ", <b>" + linksCount + "</b> " + word("article") + linksCount.plural();
    },

    search : function()
    {
        var keyword = document.querySelector("#keyword").value, url, currentLinks, resultsCount = 0, linkTitle, filteredLinks, output = [];

        if(keyword.length == 0) Journal.showAll();

        else
        {
            for(url in Journal.links)
            {
                currentLinks = Journal.links[url];
                filteredLinks = [];

                for(i = 0; i < currentLinks.length; i++)
                {
                    if(currentLinks[i][1].toLowerCase().indexOf(keyword.toLowerCase()) > -1)
                    {
                        resultsCount++;
                        filteredLinks.push(currentLinks[i]);
                    }
                }

                if(filteredLinks.length > 0)
                    output[url] = filteredLinks;
            }

            if(resultsCount > 0)
                Journal.displayLinks(output, keyword);

            else
                document.querySelector(".links").innerHTML = word("No result for") + " '" + keyword + "'.";
        }
    },

    showAll : function()
    {
        Journal.displayLinks(Journal.links);
    }
};

window.onscroll = function()
{
    var headerNeedsShrinking = document.body.scrollTop > 25 || document.documentElement.scrollTop > 25;

    if(headerNeedsShrinking) document.querySelector("header").classList.add("shrinked");
    else document.querySelector("header").classList.remove("shrinked");
}

window.addEventListener("load", Journal.refresh);