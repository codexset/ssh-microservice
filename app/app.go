package app

import (
	"google.golang.org/grpc"
	"net"
	"net/http"
	_ "net/http/pprof"
	"ssh-microservice/app/controller"
	"ssh-microservice/app/manage"
	"ssh-microservice/app/types"
	pb "ssh-microservice/router"
)

type App struct {
	option *types.Config
}

func New(config types.Config) *App {
	app := new(App)
	app.option = &config
	return app
}

func (app *App) Start() (err error) {
	// Turn on debugging
	if app.option.Debug {
		go func() {
			http.ListenAndServe(":6060", nil)
		}()
	}
	// Start microservice
	listen, err := net.Listen("tcp", app.option.Listen)
	if err != nil {
		return
	}
	server := grpc.NewServer()
	manager := manage.NewClientManager()
	pb.RegisterRouterServer(
		server,
		controller.New(manager),
	)
	server.Serve(listen)
	return
}
