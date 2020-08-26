package controller

import (
	"context"
	pb "ssh-microservice/router"
)

func (c *controller) Delete(ctx context.Context, params *pb.DeleteParameter) (*pb.Response, error) {
	err := c.manager.Delete(params.Identity)
	if err != nil {
		return c.response(err)
	}
	return c.response(nil)
}
