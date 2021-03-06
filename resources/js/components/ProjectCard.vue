<template>
    <v-card class="project-card">
        <v-card-title class="project-card__header">
            <div v-if="!edit" class="project-card__title">
                <div class="project-card__title-text">{{ name }}</div>
            </div>
            <v-form v-else class="project-card__edit" ref="projectCardEdit" @submit.prevent>
                <v-text-field v-model="editName" label="Project name" :rules="[rules.required, rules.maxLength]"
                              validate-on-blur></v-text-field>
            </v-form>
            <div class="project-card__controls">
                <v-btn v-if="!edit" @click="edit = !edit" color="primary">
                    <v-icon>mdi-pencil</v-icon>
                </v-btn>
                <v-btn v-else @click="saveProject" color="primary">Save</v-btn>
                <v-btn @click="deleteProject" color="primary">
                    <v-icon>mdi-delete</v-icon>
                </v-btn>
            </div>
        </v-card-title>
        <div v-if="id" class="project-card__task-management">
            <task-add :project-id="id" @create="addTask" :priority="tasks.length + 1"></task-add>
            <v-divider></v-divider>
            <v-list v-if="tasks" class="project-card__task-list">
                <draggable v-model="editTasks" @end="dragEnd">
                    <transition-group name="fade" mode="out-in">
                        <task-item v-for="(task, index) in editTasks" :key="task.id"
                                   :id="task.id" :name="task.name" :project-id="task.project_id"
                                   :status="task.status" :deadline="task.deadline" :priority="task.priority"
                                   @update="updateTask(index, $event)" @delete="deleteTask(index)"
                        ></task-item>
                    </transition-group>
                </draggable>
            </v-list>
        </div>
    </v-card>
</template>

<script>
  import validation from '../mixins/validation';
  import draggable from 'vuedraggable';

  export default {
    name: 'ProjectCard',
    mixins: [validation],
    components: {
      draggable,
    },
    props: {
      name: {
        type: String,
        required: true,
      },
      id: {
        type: Number,
        required: false,
        default: null,
      },
      tasks: {
        type: Array,
        required: false,
        default: () => [],
      },
    },
    data() {
      return {
        edit: this.id === null,
        editName: this.name,
        editTasks: this.tasks,
      };
    },
    methods: {
      saveProject() {
        if (!this.$refs['projectCardEdit'].validate()) {
          return;
        }
        if (this.id === null) {
          this.create();
        } else {
          this.update();
        }
      },
      deleteProject() {
        if (this.id) {
          axios.delete('/api/projects/' + this.id).then(() => this.$emit('delete')).catch(error => {
            this.$store.commit('errorMessage', error.response.data.message);
          });
        } else {
          this.$emit('delete');
        }
      },
      update() {
        axios.put('/api/projects/' + this.id, {name: this.editName}).then(response => {
          let project = response.data;
          project.tasks = this.editTasks;
          this.$emit('update', response.data);
          this.edit = false;
        }).catch(error => {
          this.$store.commit('errorMessage', error.response.data.message);
        });
      },
      create() {
        axios.post('/api/projects', {name: this.editName}).then(response => {
          let project = response.data;
          project.tasks = this.editTasks;
          this.$emit('update', project);
          this.edit = false;
        }).catch(error => {
          this.$store.commit('errorMessage', error.response.data.message);
        });
      },
      addTask(data) {
        this.editTasks.unshift(data);
        this.$emit('update', {id: this.id, name: this.name, tasks: this.editTasks});
      },
      updateTask(index, data) {
        this.$set(this.editTasks, index, data);
        this.$emit('update', {id: this.id, name: this.name, tasks: this.editTasks});
      },
      deleteTask(index) {
        this.editTasks.splice(index, 1);
        this.$emit('update', {id: this.id, name: this.name, tasks: this.editTasks});
      },
      dragEnd() {
        this.editTasks.forEach((task, index) => {
          axios.put('/api/tasks/' + task.id, {
            name: task.name,
            status: task.status,
            deadline: task.deadline,
            priority: this.tasks.length - index,
            project_id: task.project_id,
          }).then(() => {
            this.$set(this.editTasks, index, task);
            this.$emit('update', {id: this.id, name: this.name, tasks: this.editTasks});
          }).catch(error => {
            console.log(error);
            this.$store.commit('errorMessage', error.response.data.message);
          });
        });
      },
    },
  };
</script>

<style lang="scss" src="./ProjectCard.scss"></style>
