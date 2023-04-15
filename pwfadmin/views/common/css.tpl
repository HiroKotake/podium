    <style type="text/css">
    main {
        margin-left: 2em;   /* 左マージン */
    }
    .font1 {
        font-size: 24px;    /* フォントサイズ */
        font-weight: bold;  /* 太文字 */
    }
    .tabspace1 {
        margin: 0px 10px 30px 2em;  /* マージン */
    }

    /* -------------------- */
    /* ▼メニューバーの装飾 */
    /* -------------------- */
    ul.ddmenu {
        margin: 0px;               /* メニューバー外側の余白(ゼロ) */
        padding: 0px 0px 0px 15px; /* メニューバー内側の余白(左に15px) */
        background-color: #0e90e7f0; /* バーの背景色 */
    }

    /* -------------------------- */
    /* ▼メインメニュー項目の装飾 */
    /* -------------------------- */
    ul.ddmenu li {
        width: 125px;          /* メニュー項目の横幅(125px) */
        display: inline-block; /* ★横並びに配置する */
        list-style-type: none; /* ★リストの先頭記号を消す */
        position: relative;    /* ★サブメニュー表示の基準位置にする */
    }
    ul.ddmenu a {
        background-color: #0e90e7f0; /* メニュー項目の背景色 */
        color: white;              /* メニュー項目の文字色(白色) */
        line-height: 40px;         /* メニュー項目のリンクの高さ(40px) */
        text-align: center;        /* メインメニューの文字列の配置(中央寄せ) */
        text-decoration: none;     /* メニュー項目の装飾(下線を消す) */
        font-weight: bold;         /* 太字にする */
        display: block;            /* ★項目内全域をリンク可能にする */
        transition: all .3s;
    }
    ul.ddmenu a:hover {
        background-color: #0a6cad97; /* メニュー項目にマウスが載ったときの背景色 */
        color: whitesmoke;            /* メニュー項目にマウスが載ったときの文字色 */
        transition: all .3s;
    }

    /* ---------------------------------- */
    /* ▼サブメニューがある場合に開く処理 */   /* ※サブメニューが1階層しか存在しない場合の記述 */
    /* ---------------------------------- */
    ul.ddmenu li:hover ul {
        display: block;      /* ★マウスポインタが載っている項目の内部にあるリストを表示する */
    }

    /* -------------------- */
    /* ▼サブメニューの装飾 */
    /* -------------------- */
    ul.ddmenu ul {
        margin: 0px;         /* ★サブメニュー外側の余白(ゼロ) */
        padding: 0px;        /* ★サブメニュー内側の余白(ゼロ) */
        display: none;       /* ★標準では非表示にする */
        position: absolute;  /* ★絶対配置にする */
        transition: all .5s;
    }

    /* ------------------------ */
    /* ▼サブメニュー項目の装飾 */
    /* ------------------------ */
    ul.ddmenu ul li {
        width: 125px;               /* サブメニュー1項目の横幅(135px) */
        border-top: 1px solid white; /* 項目上側の枠線(白色で1pxの実線) */
        transition: all .3s;
    }
    ul.ddmenu ul li a {
        line-height: 35px;     /* サブメニュー1項目の高さ(35px) */
        text-align: left;      /* 文字列の配置(左寄せ) */
        padding-left: 5px;     /* 文字列前方の余白(5px) */
        font-weight: normal;   /* 太字にはしない */
        transition: all .3s;
    }
    ul.ddmenu ul li a:hover {
        background-color: #0a6cad97; /* サブメニュー項目にマウスが載ったときの背景色 */
        color: whitesmoke;            /* サブメニュー項目にマウスが載ったときの文字色 */
        transition: all .3s;
    }

    /* ------------------------ */
    /* ▼テーブル項目の装飾 */
    /* ------------------------ */
    table {
        margin: 0px 0px 0px 2em;        /* マージン */
    }
    table > caption {
        font-size: 24px;                /* フォントサイズ */
        font-weight: bold;              /* 太文字 */
    }
    tr {
        background-color: #eaf4fc;      /* 背景色 */
    }
    tr:nth-child(odd) {
        background-color: #bce2e8;      /* 背景色 */
    }
    td {
        padding: 0px 1em 0px 1em;       /* マージン */
        height: 26px;                   /* 高さ */
    }

    /* ------------------------ */
    /* ▼フォーム項目の装飾 */
    /* ------------------------ */
    .formfont {
        font-size: 14pt;            /* フォントサイズ */
    }
    .btnSubmit {
        display: inline-block;
        border-radius: 5%;          /* 角丸 */
        font-size: 12pt;            /* 文字サイズ */
        text-align: center;         /* 文字位置 */
        cursor: pointer;            /* カーソル */
        padding: 4px 17px;          /* 余白 */
        margin: 1em 0px 0px 0px;    /* マージン */
        background: #1ea0e7;        /* 背景色 */
        color: #ffffff;             /* 文字色 */
        line-height: 1em;           /* 1行の高さ  */
        transition: .3s;            /* なめらか変化 */
        border: 2px solid #1ea0e7;  /* 枠の指定 */
    }
    .btnSubmit:hover {
        color: #1ea0e7;         /* 背景色 */
        background: #ffffff;    /* 文字色 */
    }

    .btnJump {
        display: inline-block;
        height: 20px;               /* 高さ */
        font-size: 10pt;            /* 文字サイズ */
        text-align: center;         /* 文字位置 */
        cursor: pointer;            /* カーソル */
        padding: 2px 6px 2px 6px;   /* 余白 */
        margin-left: 2em;           /* 左マージン */
        margin-bottom: 20px;        /* 下マージン */
        background: #1ea0e7;        /* 背景色 */
        color: #ffffff;             /* 文字色 */
        line-height: 1em;           /* 1行の高さ */
        transition: .3s;            /* なめらか変化 */
        border: 2px solid #1ea0e7;  /* 枠の指定 */
    }
    .btnJump:hover {
        color: #1ea0e7;         /* 背景色 */
        background: #ffffff;    /* 文字色 */
    }

    .btnJump2 {
        display: inline-block;
        height: 20px;               /* 高さ */
        width: 60px;                /* 幅 */
        font-size: 10pt;            /* 文字サイズ */
        text-align: center;         /* 文字位置 */
        cursor: pointer;            /* カーソル */
        padding: 2px 2px 2px 2px;   /* 余白 */
        margin-bottom: 2px;        /* 下マージン */
        background: #bc64a4;        /* 背景色 */
        color: #ffffff;             /* 文字色 */
        line-height: 1em;           /* 1行の高さ  */
        transition: .3s;            /* なめらか変化 */
        border: 1px solid #000000;  /* 枠の指定 */
    }
    .btnJump2:hover {
        color: #000000;         /* 文字色 */
        background: #cca6bf;    /* 背景色 */
    }
    .btnJump2off {
        display: inline-block;
        height: 20px;               /* 高さ */
        width: 60px;                /* 幅 */
        font-size: 10pt;            /* 文字サイズ */
        text-align: center;         /* 文字位置 */
        cursor: pointer;            /* カーソル */
        padding: 2px 2px 2px 2px;   /* 余白 */
        margin-bottom: 2px;        /* 下マージン */
        background: #cca6bf;        /* 背景色 */
        color: #ffffff;             /* 文字色 */
        line-height: 1em;           /* 1行の高さ  */
        transition: .3s;            /* なめらか変化 */
        border: 1px solid #000000;  /* 枠の指定 */
    }

    </style>
