import { Component, OnInit } from '@angular/core';
import { Router, ActivatedRoute, Params } from '@angular/router';
import { UserService } from '../services/user.service';
import { TaskService } from '../services/task.service';
import { Task } from '../models/task';

@Component({
    selector: 'task-edit',
    templateUrl: '../views/task.new.html',
    providers: [UserService, TaskService]
})

export class TaskEditComponent implements OnInit {
    public page_title: string;
    public identity;
    public token;
    public task: Task;
    public status_task;
    public loading;

    constructor (
        private _route: ActivatedRoute,
        private _router: Router,
        private _userService: UserService,
        private _taskService: TaskService
    ) {
        this.page_title = 'Modificar tarea';
        this.identity = this._userService.getIdentity();
        this.token = this._userService.getToken();
    }

    ngOnInit() {
        if (this.identity == null && !this.identity.sub) {
            this._router.navigate(['/login']);
        } else {
            this.getTask();
        }
    }

    getTask() {
        this.loading = 'show';
        this._route.params.forEach((params: Params) => {
            let id = +params['id'];
    
            this._taskService.getTask(this.token, id).subscribe(
                response => {
                    //this.task = response.data;
                    //this.status_task = response.status;
                    if (response.status == 'success') {
                        if (response.task.user.id == this.identity.sub) {
                            this.task = response.task;
                            this.loading = 'hide';
                            console.log(this.task);
                        } else {
                            this._router.navigate(['/']);
                        }
                    } else {
                        this._router.navigate(['/login']);
                    }
                    //console.log(response);
                },
                error => {
                    console.log(<any>error);
                }
            );
        });
    }

    onSubmit() {
        console.log(this.task);
        this._route.params.forEach((params: Params) => {
            let id = +params['id'];
            this._taskService.update(this.token, this.task, id).subscribe(
                response => {
                    this.status_task = response.status;
                    if (this.status_task != "success") {
                        this.status_task = 'error';
                    } else {
                        this.task = response.data;
                        //this._router.navigate(['/task', this.task.id]);
                        this._router.navigate(['/']);
                    }
                },
                error => {
                    console.log(<any>error);
                }
            );
        });
    }
}