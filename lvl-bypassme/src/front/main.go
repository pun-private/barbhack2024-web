package main

import (
    "fmt"
    "io/ioutil"
    "net/http"
    "strings"
    "os"
)

func proxify(uri string) string {
    resp, _ := http.Get("http://proxy.internal" + uri)
    defer resp.Body.Close()
    body, _ := ioutil.ReadAll(resp.Body);
    return string(body)
}

func main() {
    http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
        if (r.URL.Query().Get("proxydebug") != "yesplz") {
            content, _ := ioutil.ReadFile("/app/main.go")
            fmt.Fprintf(w, string(content)); return
        }

        w.Header().Set("Content-Type", "text/html; charset=utf-8")
        ip := strings.Split(r.RemoteAddr, ":")[0]
        if r.URL.Query().Get("token") != "" && ip != "127.128.129.130" {
            fmt.Fprintf(w, "Not authorized."); return
        }
        fmt.Fprintf(w, proxify(r.RequestURI))
    })

    http.HandleFunc("/news", func(w http.ResponseWriter, r *http.Request) {
        file := r.URL.Query().Get("read");
        if strings.Contains(file, "..") || strings.Contains(file, "/") || strings.Contains(file, ";") || strings.Contains(file, "&") || strings.Contains(file, "|") {
            fmt.Fprintf(w, "Attack detected."); return
        }

        w.Header().Set("Content-Type", "text/html; charset=utf-8")
        if file == "" {
            fmt.Fprintf(w, proxify("/?token="+os.Getenv("PROXY_SECRET")+"&url=http://fileserver.internal/ls?dir=news/")); return 
        }
        fmt.Fprintf(w, proxify("/?token="+os.Getenv("PROXY_SECRET")+"&url=http://fileserver.internal/cat?file=news/"+file))
    })

    fmt.Println("Go API listening on port 8000...")
    http.ListenAndServe(":8000", nil);
}
