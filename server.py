#!/usr/bin/env python3
"""
iOS Web Studio - Multi-threaded Dual-Stack (IPv4 + IPv6) Local Server
Permite accesul local (localhost, 127.0.0.1, [::1]) și prin rețea de pe telefon.
"""
import http.server
import socketserver
import os
import sys
import socket

PORT = 8080
DIRECTORY = os.path.dirname(os.path.abspath(__file__))

def get_local_ip():
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
        s.connect(("8.8.8.8", 80))
        ip = s.getsockname()[0]
        s.close()
        return ip
    except Exception:
        return "127.0.0.1"

class CustomHandler(http.server.SimpleHTTPRequestHandler):
    def __init__(self, *args, **kwargs):
        super().__init__(*args, directory=DIRECTORY, **kwargs)

    def end_headers(self):
        self.send_header('Access-Control-Allow-Origin', '*')
        self.send_header('Cache-Control', 'no-cache, no-store, must-revalidate')
        self.send_header('Pragma', 'no-cache')
        self.send_header('Expires', '0')
        super().end_headers()

    def do_GET(self):
        clean_path = self.path.split('?')[0]
        if clean_path in ('', '/', '/studio', '/learn', '/templates'):
            self.path = '/index.html'
        try:
            return super().do_GET()
        except (BrokenPipeError, ConnectionResetError):
            pass

    def log_message(self, format, *args):
        sys.stdout.write(f"[{self.log_date_time_string()}] {args[0]} - {args[1]} {args[2]}\n")
        sys.stdout.flush()

class ThreadedHTTPServer6(socketserver.ThreadingMixIn, socketserver.TCPServer):
    address_family = socket.AF_INET6
    allow_reuse_address = True
    daemon_threads = True

    def server_bind(self):
        try:
            self.socket.setsockopt(socket.IPPROTO_IPV6, socket.IPV6_V6ONLY, 0)
        except Exception:
            pass
        super().server_bind()

class ThreadedHTTPServer4(socketserver.ThreadingMixIn, socketserver.TCPServer):
    address_family = socket.AF_INET
    allow_reuse_address = True
    daemon_threads = True

def run_server(port=PORT):
    local_ip = get_local_ip()
    server_class = ThreadedHTTPServer6
    bind_addr = ("::", port)

    try:
        httpd = server_class(bind_addr, CustomHandler)
    except Exception as e:
        # Fallback to IPv4 if dual-stack IPv6 is not permitted
        server_class = ThreadedHTTPServer4
        bind_addr = ("0.0.0.0", port)
        httpd = server_class(bind_addr, CustomHandler)

    print("=" * 64, flush=True)
    print(" 🍏  iOS Web Studio & Learning Center is LIVE!", flush=True)
    print("=" * 64, flush=True)
    print(f" 👉 Localhost (IPv6 / Chrome): http://localhost:{port}", flush=True)
    print(f" 👉 Localhost (IPv4 direct):   http://127.0.0.1:{port}", flush=True)
    print(f" 📱 De pe alt dispozitiv:      http://{local_ip}:{port}", flush=True)
    print(f" 📁 Director deservit:         {DIRECTORY}", flush=True)
    print("=" * 64, flush=True)
    print(" Apasă Ctrl+C pentru a opri serverul.\n", flush=True)

    try:
        httpd.serve_forever()
    except KeyboardInterrupt:
        print("\nServerul a fost oprit.", flush=True)
        httpd.server_close()

if __name__ == "__main__":
    p = int(sys.argv[1]) if len(sys.argv) > 1 else PORT
    run_server(p)
