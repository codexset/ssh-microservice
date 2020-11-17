package schema

import (
	"os"
	"ssh-microservice/config/options"
	"testing"
)

var schema *Schema

func TestMain(m *testing.M) {
	os.Chdir("../../..")
	schema = New("./config/autoload/")
	os.Exit(m.Run())
}

func TestSchema_Update(t *testing.T) {
	err := schema.Update(options.ClientOption{
		Identity:   "test",
		Host:       "127.0.0.1",
		Port:       22,
		Username:   "root",
		Password:   "123456",
		PrivateKey: "",
		Passphrase: "",
		Tunnels:    nil,
	})
	if err != nil {
		t.Fatal(err)
	}
}

func TestSchema_Lists(t *testing.T) {
	_, err := schema.Lists()
	if err != nil {
		t.Fatal(err)
	}
}

func TestSchema_Delete(t *testing.T) {
	err := schema.Delete("test")
	if err != nil {
		t.Fatal(err)
	}
}
