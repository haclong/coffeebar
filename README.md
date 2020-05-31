Coffeebar
=========

A small module experimenting how to use event manager to build an application

Introduction
=======

I have read few weeks ago (it was more months i believe) a very quick tutorial about how to build an application based on Domain Driven Developement AND Events Driven Development. It was really interesting because i wasn't then very familiar with Event Driven Development. The tutorial was written for .NET framework and the use case presented is a kind of coffee bar (or snack bar as you like).

I've decided to start with the same use case, based on the .NET tutorial and write the corresponding module with PHP, with Zend Framework 2.

Testing gocd push too

The Coffee Bar
=======

Rules are simple :
* When new clients is coming in, waiters has to __open a new tab__. We don't really care for customers since we won't manage the customers, but we want to know more about our tabs. So our domain entities will be revolving around the tabs and not the customers. 
* Clients can __order food and drinks__. But we have to make sure their tab is already opened so they can proceed with placing an order.
* Drinks can __be served__ but foods have to __be prepared__ before __being served__.
* Tab will __be closed__ once the __invoice has been paid__.

Waiters have to :
* track the active tabs
* serve the drinks 
* serve the food
* take the invoice once every items has been served
* close the tab

Cook has to :
* prepare the food
* mark the food prepared so it can be served

The whole things is articulate around "commands" and "events". Command is __doing thing__ and Event is __thing being done__. So basically, for one command, there's one event. Listeners will listen either to commands or to events. 

The translation
=======

Translating the .NET Framework tutorial into Zend Framework 2 architecture were fun and very useful. There's some part in the .NET tutorial i don't really get it right maybe because there's a dependency to the .NET framework i don't know or maybe because the authors try to promote their method but i had to finally adapt the code to ZF2. 


You can find detailed explanations on my blog here : http://www.haclong.org/en/content/g%C3%A9rer-un-caf%C3%A9-avec-une-programmation-%C3%A9v%C3%A9nementielle.html

(i messed up with the languages but you should find explanations both in french and in english for this project)
