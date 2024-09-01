/**
 * AnderShell - Just a small CSS demo
 *
 * Copyright (c) 2011-2013, Anders Evenrud <andersevenrud@gmail.com>
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met: 
 * 
 * 1. Redistributions of source code must retain the above copyright notice, this
 *    list of conditions and the following disclaimer. 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution. 
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
 (function() {

  var $output;
  var _inited = false;
  var _locked = false;
  var _buffer = [];
  var _obuffer = [];
  var _ibuffer = [];
  var _prompt = function() { return "$ / > "; };
  var _history = [];
  var _hindex = -1;
  var _lhindex = -1;

  var _commands = {

    about: function() {
      return ([
        "We, at Transparent Culture, care about everyone's privacy. Our aim is to develop tools to stay anonymous.",
        "For more transparency, we shared with the community our project architecture and our web server configuration",
        "used in production. (◕‿◕)♡",
        "",
        "We have released our first module `sha1img` in our *race* against website tracking. You need to get the signature",
        "of an image without downloading it ? (⌐■_■) Use our *unique* tool to do so !",
      ]).join("\n");
    },

    help: function() {

      return ([
        '',
        '* help                                 (•ิ_•ิ)?',
        '* about                                Learn more about our project',
        '* security                             Report a vulnerability',
        '* tree                                 List the project\'s directory structure (for transparency)',
        '* technology                           List the technologies we are using (for transparency)',
        '* template <name>                      Get one of our configuration used in production (for transparency)',
        '* sha1img <url>                        Generate the sha1 signature of a remote image (.jpg|.gif|.png)',
        '* clear                                Clears the screen',
        ''
      ]).join("\n");
    },
        
    security: function() {
      return ([
        "We take security threats very seriously and if you thiNk yOu fOund a vulneraBility, please send us the content of the",
        "secret file stored in the `security` folder.",
        "",
        "Good luck ! (° ͜ʖ͡°)╭∩╮"
      ]).join("\n");
    },

    tree: function() {
      return ([
        "",
        "(✿˵•́ ᴗ •̀˵)",
        "┌── includes/ ",
        "│   └── ... # not open sourced yet",
        "│",
        "├── public/",
        "│   ├── css/",
        "│   ├── js/",
        "│   ├── tmp/",
        "│   │   └── ... # temporary files for our modules",
        "│   ├── favicon.ico",
        "│   └── index.php",
        "│",
        "├── security/",
        "│   └── ... # if you hack us, send us the proof listed here",        
        "│",
        "├── templates/",
        "│   └── nginx.conf",
        "│", 
        "├── composer.json",
        "└── composer.lock",
      ]).join("\n");
    },

    technology: function() {
      return ([
        "All our tools are up-to-date ! ᕙ(⇀‸↼‶)ᕗ",
        "",
        " - Nginx",
        " - PHP 8.3",
        " - Composer",
        " - JQuery",
        ]).join("\n");
    },

    template: function(name) {
      if ( !name ) {
        return ([
          "(＃＞＜) You need to supply a template name ! Available choices :",
          " - nginx"
        ]).join("\n");
      }

      switch ( name.toLowerCase() ) {
        case 'nginx' :
          window.open('/templates/nginx.conf');
          return (["(ﾉ◕ヮ◕)ﾉ*:･ﾟ✧ `nginx.conf` sent."]).join("\n");

        default :
          return (["(＃＞＜) Unknown template name."]).join("\n");
      }
    },

    sha1img: function(url) {
      if ( !url ) {
        return ([
          "(⌒_⌒;) You need to supply an url !",
          " > sha1img https://via.placeholder.com/350x150.png"
        ]).join("\n");
      }

      print("Getting content from URL....................................................\n");

      var response = $.ajax({
        type: "POST",
        url: '/module/sha1img',
        data: {url : url},
        async: false
      }).responseText;

      return [response].join("\n");
    },

    clear: function() {
      return false;
    }

  };

  /////////////////////////////////////////////////////////////////
  // UTILS
  /////////////////////////////////////////////////////////////////

  function setSelectionRange(input, selectionStart, selectionEnd) {
    if (input.setSelectionRange) {
      input.focus();
      input.setSelectionRange(selectionStart, selectionEnd);
    }
    else if (input.createTextRange) {
      var range = input.createTextRange();
      range.collapse(true);
      range.moveEnd('character', selectionEnd);
      range.moveStart('character', selectionStart);
      range.select();
    }
  }

  function format(format) {
    var args = Array.prototype.slice.call(arguments, 1);
    var sprintfRegex = /\{(\d+)\}/g;

    var sprintf = function (match, number) {
      return number in args ? args[number] : match;
    };

    return format.replace(sprintfRegex, sprintf);
  }


  function padRight(str, l, c) {
    return str+Array(l-str.length+1).join(c||" ")
  }

  function padCenter(str, width, padding) {
    var _repeat = function(s, num) {
      for( var i = 0, buf = ""; i < num; i++ ) buf += s;
      return buf;
    };

    padding = (padding || ' ').substr( 0, 1 );
    if ( str.length < width ) {
      var len     = width - str.length;
      var remain  = ( len % 2 == 0 ) ? "" : padding;
      var pads    = _repeat(padding, parseInt(len / 2));
      return pads + str + pads + remain;
    }

    return str;
  }

  window.requestAnimFrame = (function(){
    return  window.requestAnimationFrame       ||
    window.webkitRequestAnimationFrame ||
    window.mozRequestAnimationFrame    ||
    function( callback ){
      window.setTimeout(callback, 1000 / 60);
    };
  })();

  /////////////////////////////////////////////////////////////////
  // SHELL
  /////////////////////////////////////////////////////////////////

  (function animloop(){
    requestAnimFrame(animloop);

    if ( _obuffer.length ) {
      $output.value += _obuffer.shift();
      _locked = true;

      update();
    } else {
      if ( _ibuffer.length ) {
        $output.value += _ibuffer.shift();

        update();
      }

      _locked = false;
      _inited = true;
    }
  })();

  function print(input, lp) {
    update();
    _obuffer = _obuffer.concat(lp ? [input] : input.split(''));
  }

  function update() {
    $output.focus();
    var l = $output.value.length;
    setSelectionRange($output, l, l);
    $output.scrollTop = $output.scrollHeight;
  }

  function clear() {
    $output.value = '';
    _ibuffer = [];
    _obuffer = [];
    print("");
  }

  function command(cmd) {
    print("\n");
    if ( cmd.length ) {
      var a = cmd.split(' ');
      var c = a.shift();
      if ( c in _commands ) {
        var result = _commands[c].apply(_commands, a);
        if ( result === false ) {
          clear();
        } else {
          print(result || "\n", true);
        }
      } else {
        print("(;O_O)	Unknown command: " + c);
      }

      _history.push(cmd);
    }
    print("\n\n" + _prompt());

    _hindex = -1;
  }

  function nextHistory() {
    if ( !_history.length ) return;

    var insert;
    if ( _hindex == -1 ) {
      _hindex  = _history.length - 1;
      _lhindex = -1;
      insert   = _history[_hindex];
    } else {
      if ( _hindex > 1 ) {
        _lhindex = _hindex;
        _hindex--;
        insert = _history[_hindex];
      }
    }

    if ( insert ) {
      if ( _lhindex != -1 ) {
        var txt = _history[_lhindex];
        $output.value = $output.value.substr(0, $output.value.length - txt.length);
        update();
      }
      _buffer = insert.split('');
      _ibuffer = insert.split('');
    }
  }

  window.onload = function() {
    $output = document.getElementById("output");
    $output.contentEditable = true;
    $output.spellcheck = false;
    $output.value = '';

    $output.onkeydown = function(ev) {
      var k = ev.which || ev.keyCode;
      var cancel = false;

      if ( !_inited ) {
        cancel = true;
      } else {
        if ( k == 9 ) {
          cancel = true;
        } else if ( k == 38 ) {
          nextHistory();
          cancel = true;
        } else if ( k == 40 ) {
          cancel = true;
        } else if ( k == 37 || k == 39 ) {
          cancel = true;
        }
      }

      if ( cancel ) {
        ev.preventDefault();
        ev.stopPropagation();
        return false;
      }

      if ( k == 8 ) {
        if ( _buffer.length ) {
          _buffer.pop();
        } else {
          ev.preventDefault();
          return false;
        }
      }

      return true;
    };

    $output.onkeypress = function(ev) {
      ev.preventDefault();
      if ( !_inited ) {
        return false;
      }

      var k = ev.which || ev.keyCode;
      if ( k == 13 ) {
        var cmd = _buffer.join('').replace(/\s+/, ' ');
        _buffer = [];
        command(cmd);
      } else {
        if ( !_locked ) {
          var kc = String.fromCharCode(k);
          _buffer.push(kc);
          _ibuffer.push(kc);
        }
      }

      return true;
    };

    $output.onfocus = function() {
      update();
    };

    $output.onblur = function() {
      update();
    };

    window.onfocus = function() {
      update();
    };

    print("Initializing AnonShell v42.1 ", );
    print("................................................................................\n", );
    print("Copyright (c) 20XY -  Transparent Culture <null@zer0.void>\n\n", true);

    print("                  @@@  @@@  @@@  @@@@@@@@  @@@        @@@@@@@   @@@@@@   @@@@@@@@@@   @@@@@@@@                  \n", true);
    print("                  @@@  @@@  @@@  @@@@@@@@  @@@       @@@@@@@@  @@@@@@@@  @@@@@@@@@@@  @@@@@@@@                  \n", true);
    print("                  @@!  @@!  @@!  @@!       @@!       !@@       @@!  @@@  @@! @@! @@!  @@!                       \n", true);
    print("                  !@!  !@!  !@!  !@!       !@!       !@!       !@!  @!@  !@! !@! !@!  !@!                       \n", true);
    print("                  @!!  !!@  @!@  @!!!:!    @!!       !@!       @!@  !@!  @!! !!@ @!@  @!!!:!                    \n", true);
    print("                  !@!  !!!  !@!  !!!!!:    !!!       !!!       !@!  !!!  !@!   ! !@!  !!!!!:                    \n", true);
    print("                  !!:  !!:  !!:  !!:       !!:       :!!       !!:  !!!  !!:     !!:  !!:                       \n", true);
    print("                  :!:  :!:  :!:  :!:        :!:      :!:       :!:  !:!  :!:     :!:  :!:                       \n", true);
    print("                   :::: :: :::    :: ::::   :: ::::   ::: :::  ::::: ::  :::     ::    :: ::::                  \n", true);
    print("                    :: :  : :    : :: ::   : :: : :   :: :: :   : :  :    :      :    : :: ::                   \n", true);
    print("\n", true);

    print("                --------------------------------------------------------------------------------\n", true);
    print("                       !! Disclaimer : We cannot share our source code at the moment !!\n", true);
    print("                --------------------------------------------------------------------------------\n", true);
    print("                                               *You can trust us*\n", true);

    print("\n\n\n", true);
    print("Type 'help' for a list of available commands.", true);
    print("\n\n" + _prompt());

  };

})();
